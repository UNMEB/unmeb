<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use Illuminate\Console\Command;

class RecalculateAccountBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'accounts:recalculate-balances';

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
        $accounts = Account::withoutGlobalScopes()->get();

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

        $this->info('Account balances recalculated successfully!');
    }
}
