<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class PendingTransactionListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $transactions = Transaction::with('institution', 'account')->where('is_approved', 0)->get();
        return [
            'transactions' => $transactions
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Pending Institution Transactions';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('transactions', [
                TD::make('id', 'Transaction ID'),
                TD::make('account_id', 'Institution')->render(function (Transaction $data) {
                    return $data->institution->name;
                }),
                TD::make('type', 'Transaction Type')->render(function ($data) {
                    return $data->type == 'credit' ? 'Account Credit' : 'Account Debit';
                }),
                TD::make('method', 'Transaction Method')->render(function ($data) {
                    return $data->method == 'bank' ? 'Bank Transfer/Payment' : 'Mobile Money';
                }),
                TD::make('amount', 'Amount')->render(function ($data) {
                    return 'Ush ' . number_format($data->amount);
                }),
                TD::make('is_approved', 'Approval Status')->render(function ($data) {
                    return $data->is_approved == 1 ? 'Approved' : 'Pending';
                }),
                TD::make('approved_by', 'Approved By')->render(function (Transaction $data) {
                    return $data->is_approved == 1 ? $data->approvedBy->name : 'Not Approved';
                }),
                TD::make('actions', 'Actions')->render(function (Transaction $data) {
                    return Button::make('Approve')->type(Color::SUCCESS)
                        ->method('approve', [
                            'id' => $data->id
                        ])->disabled($data->is_approved == 1)
                        ->canSee(auth()->user()->inRole('system-admin') || auth()->user()->inRole('accountant'));
                })->alignCenter()
            ])
        ];
    }

    public function approve(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        $transaction->approved_by = auth()->id();
        $transaction->is_approved = 1;
        $transaction->save();

        Alert::success('Transaction has been approved and Institution account credited');

        return back();
    }
}
