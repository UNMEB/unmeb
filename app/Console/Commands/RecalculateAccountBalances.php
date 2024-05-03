<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\Transaction;
use App\Models\Institution;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class RecalculateAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:recalculate-balances {institution_id}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate account balances based on transactions.';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        DB::beginTransaction();

        try {
            // Find orphaned registrations
            $institutionId = $this->argument('institution_id');
            $institution = Institution::withoutGlobalScopes()->findOrFail($institutionId);

            // Get all transactions related to exam registration
            $examTransactions = Transaction::withoutGlobalScopes()
                ->where('comment', 'LIKE', 'Exam Registration for student ID:%')
                ->where('institution_id', $institution->id)
                ->get();

            // Get all transactions related to NSIN registration
            $nsinTransactions = Transaction::withoutGlobalScopes()
                ->where('comment', 'LIKE', 'NSIN Registration for Student ID:%')
                ->where('institution_id', $institution->id)
                ->get();

            // Extract student IDs from transactions
            $nsinIdsFromTransactions = $nsinTransactions->pluck('comment')
                ->map(function ($comment) {
                    return Str::after($comment, 'NSIN Registration for student ID:');
                });

            // Extract student IDs from transactions
            $examIdsFromTransactions = $examTransactions->pluck('comment')
                ->map(function ($comment) {
                    return Str::after($comment, 'Exam Registration for student ID:');
                });

            // Get student registrations for the current period
            $examRegistrationPeriod = RegistrationPeriod::whereFlag(1, true)->first();

            // Get nsin student registrations for current period
            $nsinRegistrationPeriod = NsinRegistrationPeriod::whereFlag(1, true)->first();

            // Get all orphaned NSIN Registrations
            $orphanedNSINRegistrations = NsinStudentRegistration::withoutGlobalScopes()->select('nsr.student_id', 'nr.id')
                ->from('nsin_student_registrations as nsr')
                ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
                ->join('nsin_registration_periods AS nrp', function ($join)  {
                    $join->on('nrp.month','=','nr.month');
                    $join->on('nrp.year_id','=','nr.year_id');
                })
                ->where('nrp.id', $nsinRegistrationPeriod->id)
                ->where('nr.institution_id', $institution->id)
                ->whereNotIn('nsr.student_id', $nsinIdsFromTransactions)
                ->get();

            $orphanedExamRegistrations = StudentRegistration::withoutGlobalScopes()->select('sr.student_id', 'r.id')
                ->from('student_registrations as sr')
                ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
                ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
                ->where('rp.id', $examRegistrationPeriod->id)
                ->where('r.institution_id', $institution->id)
                ->whereNotIn('sr.student_id', $examIdsFromTransactions) // Exclude IDs 
                ->get();

            $this->info('Found ' . $orphanedNSINRegistrations->count() . ' orphaned NSIN registrations');

            $this->info('Found ' . $orphanedExamRegistrations->count() . ' orphaned Exam registrations');

            // Loop through orphaned registrations and delete them
            foreach ($orphanedNSINRegistrations as $orphanedRegistration) {
                $this->info('Found an orphaned NSIN registration. Deleting...');
                $deleted = NsinStudentRegistration::where([
                    'nsin_registration_id' => $orphanedRegistration->id,
                    'student_id' => $orphanedRegistration->student_id
                ])->delete();
            }

            // Loop through orphaned registrations and delete them
            foreach ($orphanedExamRegistrations as $orphanedRegistration) {
                $this->info('Found an orphaned registration. Deleting...');
                $deleted = StudentRegistration::where([
                    'registration_id' => $orphanedRegistration->id,
                    'student_id' => $orphanedRegistration->student_id
                ])->delete();
            }

            // Get all transactions (same as before)
            $examTransactions = Transaction::withoutGlobalScopes()
                ->where('comment', 'LIKE', '%Exam Registration for student ID:%')
                ->where('institution_id', $institution->id)
                ->get();

            // Get all transactions (same as before)
            $nsinTransactions = Transaction::withoutGlobalScopes()
                ->where('comment', 'LIKE', '%NSIN Registration for student ID:%')
                ->where('institution_id', $institution->id)
                ->get();

            // Filter transactions based on existence of student registration
            $nsinTransactionsToDelete = $nsinTransactions->filter(function ($transaction) use ($nsinIdsFromTransactions) {
                // Extract student ID from comment (same as before)
                $studentId = Str::after($transaction->comment, '%NSIN Registration for student ID:');
                // Check if student ID exists in registered students
                return !in_array($studentId, $nsinIdsFromTransactions->toArray());
            });

            // Filter transactions based on existence of student registration
            $examTransactionsToDelete = $examTransactions->filter(function ($transaction) use ($examIdsFromTransactions) {
                // Extract student ID from comment (same as before)
                $studentId = Str::after($transaction->comment, '%Exam Registration for student ID:');
                // Check if student ID exists in registered students
                return !in_array($studentId, $examIdsFromTransactions->toArray());
            });

            // Delete the transactions without a student registration
            foreach ($nsinTransactionsToDelete as $transaction) {
                $this->info('Found orphaned nsin transaction to delete. Deleting...');
                Transaction::where('id', $transaction->id)->delete();
            }

            // Delete the transactions without a student registration
            foreach ($examTransactionsToDelete as $transaction) {
                $this->info('Found orphaned exam transaction to delete. Deleting...');
                Transaction::where('id', $transaction->id)->delete();
            }

            $this->info('Recalculating account balances for institution: ' . $institution->institution_name);

            $account = Account::withoutGlobalScopes()->where('institution_id', $institution->id)->first();

            if (!$account) {
                $this->info('No account found for the given institution: ' . $institution->institution_name);
                return;
            }

            $this->info('Account found for institution: ' . $institution->institution_name);

            // Reset account balance to zero
            $account->balance = 0;
            $account->save();

            $this->info('Account balance reset to zero.');

            // Top up account balance with funds approved by Semei
            $approvedFunds = Transaction::withoutGlobalScopes()
                ->where('account_id', $account->id)
                ->where('status', 'approved')
                ->where('type', 'credit')
                ->where('approved_by', 273)
                ->sum('amount');

            // Set total approved funds as new account balance
            $newAccountBalance = $approvedFunds;

            // Get total debits
            $totalDebits = Transaction::withoutGlobalScopes()
                ->where('account_id', $account->id)
                ->where('status', 'approved')
                ->where('type', 'debit')
                ->sum('amount');

            // Get total credits except those already added under approved funds
            $totalCredits = Transaction::withoutGlobalScopes()
                ->where('account_id', $account->id)
                ->where('status', 'approved')
                ->where('type', 'credit')
                // Filter out reversals using WHERE NOT LIKE
                ->where('comment', 'NOT LIKE', 'Reversal of Exam Registration Fee for Student ID:%')
                ->where('comment', 'NOT LIKE', 'Reversal of NSIN Registration Fee for Student ID:%')
                ->whereNotIn('id', Transaction::withoutGlobalScopes()
                    ->where('account_id', $account->id)
                    ->where('status', 'approved')
                    ->where('approved_by', 273)
                    ->where('type', 'credit')
                    ->pluck('id'))
                ->sum('amount');

            // Deduct total debits from new account balance
            $newAccountBalance -= $totalDebits;

            // Add total credits to new account balance
            $newAccountBalance += $totalCredits;

            $account->balance = $newAccountBalance;
            $account->save();

            $this->info('Account balance recalculated successfully.');

            $this->info('Summary:');
            $this->info('--------------------------------------------');
            $this->info('Total Approved Funds: ' . number_format($approvedFunds));
            $this->info('Total Debits: ' . number_format($totalDebits));
            $this->info('Total Credits: ' . number_format($totalCredits));
            $this->info('--------------------------------------------');
            $this->info('New Account Balance: ' . number_format($newAccountBalance));
            $this->info('--------------------------------------------');

            DB::commit();

        } catch (\Throwable $th) {
            $this->info($th->getMessage());
            DB::rollBack();
        }
    }
}

