<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PendingTransactionExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Transaction::with('approvedBy')
            ->join('institutions', 'transactions.institution_id', 'institutions.id')
            ->join('accounts', 'transactions.account_id', 'accounts.id')
            ->join('users', 'transactions.approved_by', '=', 'users.id')
            ->select(
                'transactions.id',
                'institutions.institution_name',
                'transactions.type',
                'transactions.method',
                'transactions.amount',
                'transactions.status',
                'transactions.approved_by',
                'transactions.comment',
                'transaction.created_at'
            )
            ->latest('transactions.created_at')
            ->get()
            ->map(function ($transaction) {
                $transaction->amount = number_format($transaction->amount,0,'.','');
                $transaction->approved_by = $transaction->approvedBy->name;
                return $transaction;
            });
    }
    /**
     * @return array
     */
    public function headings(): array {
        return [
            'Transaction ID',
            'Institution',
            'Transaction Type',
            'Transaction Method',
            'Amount',
            'Status',
            'Approved By',
            'Comment',
            'Transaction Date'
        ];
    }
}
