<?php

namespace App\Console\Commands;

use App\Models\Account;
use App\Models\Transaction;
use App\Models\Institution;
use Artisan;
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
        // Put the application into maintenance mode
        Artisan::call('down');

        try {
            DB::beginTransaction();

            // 1. For each institution in the system, find all transactions where status is reversed and delete them
            Institution::chunk(100, function ($institutions) {
                foreach ($institutions as $institution) {
                    $account = Account::find($institution->account_id);

                    // Get the current account balance
                    $this->info('Current account balance for ' . $institution->institution_name . ' is UGX ' . number_format($account->balance));

                    // Set the account balance to zero
                    $account->balance = 0;
                    $account->save();

                    // Top up account balance with funds approved by Semei
                    $approvedFunds = Transaction::withoutGlobalScopes()
                        ->where('account_id', $account->id)
                        ->where('status', 'approved')
                        ->where('type', 'credit')
                        ->where('approved_by', 273)
                        ->sum('amount');

                    // Update account balance to new balance
                    $account->balance = $approvedFunds;
                    $account->save();

                    // Get total debits
                    $totalDebits = Transaction::withoutGlobalScopes()
                        ->where('account_id', $account->id)
                        ->where('status', 'approved')
                        ->where('type', 'debit')
                        ->sum('amount');

                    $this->info('Summary:');
                    $this->info('--------------------------------------------');
                    $this->info('Total Approved Funds: ' . number_format($approvedFunds));
                    $this->info('Total Debits: ' . number_format($totalDebits));
                    $this->info('Total Credits: ' . number_format($approvedFunds));
                    $this->info('--------------------------------------------');
                    $this->info('New Account Balance: ' . number_format($account->balance));
                    $this->info('--------------------------------------------');
                }
            });

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('An error occurred: ' . $e->getMessage());
        }

        // Bring the application back up
        Artisan::call('up');
    }
}
