<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Institution;
use Illuminate\Console\Command;

class RecalculateAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:recalculate-balances {institution_id : The ID of the institution}';

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
        
        if (!$institutionId) {
            $this->error('Institution ID is required!');
            return;
        }

        $institution = Institution::find($institutionId);

        if (!$institution) {
            $this->error('Institution not found!');
            return;
        }

        $accounts = Account::withoutGlobalScopes()->where('institution_id', $institutionId)->get();

        foreach ($accounts as $account) {
            $transactions = Transaction::withoutGlobalScopes()->where('account_id', $account->id)->get();
            $balance = 0;

            foreach ($transactions as $transaction) {
                if ($transaction->type == 'credit') {
                    $balance += $transaction->amount;
                } elseif ($transaction->type == 'debit') {
                    $balance -= $transaction->amount;
                }
            }

            $account->balance = $balance;
            $account->save();
        }

        $this->info('Account balances recalculated successfully for institution: ' . $institution->institution_name);
    }
}
