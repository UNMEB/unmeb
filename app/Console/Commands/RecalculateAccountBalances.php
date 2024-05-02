<?php

namespace App\Console\Commands;

use App\Models\Account;
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

        $account = Account::withoutGlobalScopes()->where('institution_id', $institution->id)->first();

        if (!$account) {
            $this->info('No account found for the given institution.');
            return;
        }

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

        // Get all exam transactions using comment starting with Exam Registration for student ID
        $examTransactions = Transaction::where('account_id', $account->id)
            ->where('comment', 'like', 'Exam Registration for student ID%')
            ->get();

        // Check for duplicates these will have the same comment e.g. Exam Registration for student ID: 137542
        $uniqueTransactions = $examTransactions->unique('comment');

        // If you need to delete the duplicates from the database:
        $duplicateComments = $examTransactions->pluck('comment')->duplicates()->all();
        Transaction::whereIn('comment', $duplicateComments)->delete();

        // After deleting the duplicates, deduct these funds from the account balance
        $totalExamFunds = $examTransactions->sum('amount');
        $account->balance -= $totalExamFunds;

        $account->save();

        $this->info('Account balance recalculated successfully for institution: ' . $institution->institution_name);
    }
}
