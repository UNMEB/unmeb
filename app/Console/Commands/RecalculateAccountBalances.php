<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\StudentRegistration;
use App\Models\Transaction;
use App\Models\Institution;
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
        $institutionId = $this->argument('institution_id');
        $institution = Institution::findOrFail($institutionId);

        $this->info('Recalculating account balances for institution: ' . $institution->institution_name);

        $account = Account::withoutGlobalScopes()->where('institution_id', $institution->id)->first();

        if (!$account) {
            $this->info('No account found for the given institution: ' . $institution->institution_name);
            return;
        }

        $this->info('Account found for institution: ' . $institution->institution_name);

        // Set the initial account balance to zero
        $account->balance = 0;

        // Top up account balance with funds approved by Semei
        $approvedFunds = Transaction::withoutGlobalScopes()
            ->where('account_id', $account->id)
            ->where('status', 'approved')
            ->where('approved_by', 273)
            ->where('type', 'credit')
            ->get();

        // Get the total approved funds and add them to the account
        $totalApprovedFunds = $approvedFunds->sum('amount');
        $account->balance += $totalApprovedFunds;

        $this->info('Total approved funds added: ' . $totalApprovedFunds);

        // Get all transactions for different purposes
        $transactions = Transaction::withoutGlobalScopes()
            ->where('status', 'approved')
            ->where('account_id', $account->id)
            ->get();

        // Process each transaction
        foreach ($transactions as $transaction) {
            if (Str::startsWith($transaction->comment, 'Exam Registration for student ID')) {
                $account->balance -= $transaction->amount;
                $this->info('Deducted exam funds: ' . $transaction->amount);

                // Get the student id
                $studentId = Str::of($transaction->comment)->after('student ID:')->trim();

                // Check for this student registration
                $studentRegistration = StudentRegistration::where('student_id', $studentId)
                ->latest()
                ->first();

                // Get the registration and check its registrati
                if ($studentRegistration) {
                    $registrationId = $studentRegistration->registration_id;

                    $registration = Registration::find($registrationId);

                    // Check if this $registration's period is the active 1;
                    if($registration->registration_period_id == RegistrationPeriod::whereFlag(1, true)->first()->id) {
                        // If its a match we skip
                    } else {
                        
                    }
                }

            } elseif (Str::startsWith($transaction->comment, 'NSIN Registration Fee for Student ID')) {
                $account->balance -= $transaction->amount;
                $this->info('Deducted NSIN registration fee: ' . $transaction->amount);
            } elseif (Str::startsWith($transaction->comment, 'Logbook Fee for Student ID')) {
                $account->balance -= $transaction->amount;
                $this->info('Deducted logbook fee: ' . $transaction->amount);
            } elseif (Str::startsWith($transaction->comment, 'Research Guideline Fee for Student ID')) {
                $account->balance -= $transaction->amount;
                $this->info('Deducted research guideline fee: ' . $transaction->amount);
            }
        }

        // Delete duplicate transactions
        $this->deleteDuplicateTransactions($account);

        $account->save();

        $this->info('Account balance recalculated successfully for institution: ' . $institution->institution_name);
    }

    /**
     * Delete duplicate transactions for the account.
     *
     * @param \App\Models\Account $account
     * @return void
     */
    private function deleteDuplicateTransactions(Account $account)
    {
        $transactionComments = Transaction::withoutGlobalScopes()
            ->where('status', 'approved')
            ->where('account_id', $account->id)
            ->pluck('comment');

        // Get duplicate comments
        $duplicateComments = $transactionComments->duplicates()->all();

        foreach ($duplicateComments as $comment) {
            $duplicateTransactions = Transaction::withoutGlobalScopes()
                ->where('status', 'approved')
                ->where('account_id', $account->id)
                ->where('comment', $comment)
                ->get();

            foreach ($duplicateTransactions as $transaction) {
                $this->info('Found duplicate transaction: ' . $transaction->comment . ', amount: ' . $transaction->amount);
            }
        }

        // Delete duplicate transactions
        Transaction::withoutGlobalScopes()
            ->where('status', 'approved')
            ->where('account_id', $account->id)
            ->whereIn('comment', $duplicateComments)
            ->delete();

        $this->info('Deleted ' . count($duplicateComments) . ' duplicate transactions');
    }
}
