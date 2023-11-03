<?php

namespace App\Observers;

use App\Mail\NotifyAccountsAboutPendingTransaction;
use App\Mail\NotifyAdminsAboutPendingTransaction;
use App\Mail\NotifyInstitutionAboutPendingTransaction;
use App\Mail\NotifyInstitutionAboutTransactionApproved;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Orchid\Platform\Models\Role;

class TransactionObserver
{

    public function created(Transaction $transaction)
    {
        if ($transaction->is_approved == 0  && $transaction->type == 'credit') {

            $adminRole = Role::firstWhere('slug', 'system-admin');
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
        if ($transaction->is_approved == 1 && $transaction->type == 'credit') {
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

        }
    }
}
