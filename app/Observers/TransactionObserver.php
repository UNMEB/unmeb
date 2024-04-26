<?php

namespace App\Observers;

use App\Mail\NotifyAccountsAboutPendingTransaction;
use App\Mail\NotifyAdminsAboutPendingTransaction;
use App\Mail\NotifyInstitutionAboutPendingTransaction;
use App\Mail\NotifyInstitutionAboutTransactionApproved;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\User;
use App\Notifications\TransactionApproved;
use Illuminate\Support\Facades\Mail;
use Orchid\Platform\Models\Role;

class TransactionObserver
{

    public function created(Transaction $transaction)
    {
        if ($transaction->status == 'pending'  && $transaction->type == 'credit') {

            $adminRole = Role::firstWhere('slug', 'administrator');
            $accountRole = Role::firstWhere('slug', 'accountant');

            $adminEmails = [];
            $accountEmails = [];

            $adminUsers = $adminRole->getUsers();
            $accountUsers = $accountRole->getUsers();

            $adminEmails = $adminUsers->pluck('email')->toArray();
            $accountEmails = $accountUsers->pluck('email')->toArray();

            // Combine admin and account emails
            $combinedEmails = array_merge($adminEmails, $accountEmails);

            try {
                Mail::to('info@unmeb.go.ug')
                ->cc($combinedEmails)
                ->send(new NotifyAccountsAboutPendingTransaction($transaction, $accountUsers));

            } catch (\Throwable $th) {
                //throw $th;
            }
        }
    }

    public function updated(Transaction $transaction): void
    {
        // Check approval status
        if ($transaction->status == 'approved' && $transaction->type == 'credit') {
            // Increment the account balance for the institution
            $transaction->account->increment('balance', $transaction->amount);

            // Notify all users that belong to this institution
            $users = $transaction->institution->users;

            // Get all user emails
            $userEmails = $users->pluck('email')->toArray();

            $institutionEmail = $transaction->institution->email;

            if ($institutionEmail) {
                Mail::to($transaction->institution->email)->cc($userEmails)->send(new NotifyInstitutionAboutTransactionApproved($transaction));
            }

            // /User::find($transaction->initiated_by)->notify(new TransactionApproved($transaction));
        }
    }

    /**
     * Log transaction activity.
     *
     * @param  \App\Models\Transaction  $transaction
     * @param  string  $action
     * @return void
     */
    protected function logTransaction(Transaction $transaction, $action)
    {
        TransactionLog::create([
            'transaction_id' => $transaction->id,
            'user_id' => $transaction->user_id,
            'action' => $action,
            'description' => "Transaction {$action}.",
        ]);
    }
}
