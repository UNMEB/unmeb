<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Models\Institution;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use NumberFormatter;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use Barryvdh\DomPDF\Facade\Pdf;

use Rmunate\Utilities\SpellNumber;

use Illuminate\Support\Str;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Support\Facades\Alert;

class TransactionListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $transactions = Transaction::with('institution', 'account')->where('is_approved', 1)->get();
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
        return 'Transactions';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
        ];
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
                    return $data->institution->institution_name;
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
                    return $data->is_approved == 1 ? optional($data->approvedBy)->name : 'Not Approved';
                }),
                TD::make('comment', 'Comment'),
                TD::make('print_receipt', 'Receipt')->render(function (Transaction $data) {
                    return Button::make('Print Receipt')
                        ->method('print', [
                            'id' => $data->id
                        ])
                        ->rawClick(false);
                })
            ])
        ];
    }

    public function print(Request $request, $id)
    {
        $transaction = Transaction::find($id);

        // Amount
        $amount = $transaction->amount;

        // Amount in words
        $amountInWords = (new NumberFormatter('en_US', NumberFormatter::SPELLOUT))->format($amount);


        // Html for address
        $address = " Plot 157 Ssebowa Road,Kiwatule, Nakawa division, <br />

        Kampala –Uganda (East Africa). <br />

        P.O. Box 3513, Kampala (Uganda).";

        $receiptData = [
            'amount' => 'Ush ' . number_format($amount),
            'amountInWords' => Str::title($amountInWords),
            'address'   => $address,
            'approvedBy' => $transaction->approvedBy->name ?? 'UNMEB OSRS',
            'institution' => $transaction->institution->institution_name,
        ];

        $pdf = Pdf::loadView('receipt', $receiptData);

        return $pdf->download('receipt.pdf');
    }



    public function currentUser(): User
    {
        return auth()->user();
    }
}
