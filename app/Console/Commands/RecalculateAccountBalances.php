<?php

namespace App\Console\Commands;

use App\Models\Account;
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
            $transactions = Transaction::withoutGlobalScopes()
                ->where('comment', 'LIKE', 'Exam Registration for student ID:%')
                ->where('institution_id', $institution->id)
                ->get();

            // Extract student IDs from transactions
            $studentIdsFromTransactions = $transactions->pluck('comment')
                ->map(function ($comment) {
                    return Str::after($comment, 'Exam Registration for student ID:');
                });

            // Get student registrations for the current period
            $registrationPeriod = RegistrationPeriod::whereFlag(1, true)->first();

            $orphanedRegistrations = StudentRegistration::withoutGlobalScopes()->select('sr.student_id', 'r.id')
                ->from('student_registrations as sr')
                ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
                ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
                ->where('rp.id', $registrationPeriod->id)
                ->where('r.institution_id', $institution->id)
                ->whereNotIn('sr.student_id', $studentIdsFromTransactions) // Exclude IDs 
                ->get();

            $this->info('Found ' . $orphanedRegistrations->count() .' orphaned registrations');

            // Loop through orphaned registrations and delete them
            foreach ($orphanedRegistrations as $orphanedRegistration) {
                $this->info('Found an orphaned registration. Deleting...');
                $deleted = StudentRegistration::where([
                    'registration_id' => $orphanedRegistration->id,
                    'student_id' => $orphanedRegistration->student_id
                ])->delete();
            }

            // Get all transactions (same as before)
            $transactions = Transaction::withoutGlobalScopes()
                ->where('comment', 'LIKE', 'Exam Registration for student ID:%')
                ->where('institution_id', $institution->id)
                ->get();

            // Filter transactions based on existence of student registration
            $transactionsToDelete = $transactions->filter(function ($transaction) use ($studentIdsFromTransactions) {
                // Extract student ID from comment (same as before)
                $studentId = Str::after($transaction->comment, 'Exam Registration for student ID:');
                // Check if student ID exists in registered students
                return !in_array($studentId, $studentIdsFromTransactions->toArray());
            });

            // Delete the transactions without a student registration
            foreach ($transactionsToDelete as $transaction) {
                $this->info('Found orphaned transaction to delete. Deleting...');
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
                ->where('approved_by', 299)
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
                    ->where('approved_by', 299)
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
            DB::rollBack();
        }
    }
}

