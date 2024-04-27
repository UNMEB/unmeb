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
        $institutions = Institution::all();

        foreach ($institutions as $institution) {
            $accounts = Account::withoutGlobalScopes()->where('institution_id', $institution->id)->get();

            foreach ($accounts as $account) {
                $transactions = Transaction::withoutGlobalScopes()
                    ->where('account_id', $account->id)
                    ->where('status', 'approved')
                    ->get();
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
}

