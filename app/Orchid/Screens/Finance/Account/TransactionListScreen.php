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
        $transactions = Transaction::with('institution', 'account')->where('status', 'approved')->latest();
        return [
            'transactions' => $transactions->paginate()
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
            ModalToggle::make('Deposit Funds')
                ->modal('depositFundsModal')
                ->method('deposit')
                ->icon('wallet'),
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

            Layout::modal('depositFundsModal', Layout::rows([
                Relation::make('institution_id')
                    ->fromModel(Institution::class, 'institution_name')
                    ->chunk(20)
                    ->title('Select Institution')
                    ->placeholder('Select an institution')
                    ->applyScope('userInstitutions')
                    ->canSee($this->currentUser()->inRole('system-admin')),

                Input::make('amount')
                    ->required()
                    ->title('Enter amount to deposit')
                    ->mask([
                        'alias' => 'currency',
                        'prefix' => 'Ush ',
                        'groupSeparator' => ',',
                        'digitsOptional' => true,
                    ])
                    ->help('Enter the exact amount paid to bank'),

                Select::make('method')
                    ->title('Select payment method')
                    ->options([
                        'bank' => 'Bank Payment',
                        'mobile_money' => 'Mobile Money'
                    ])
                    ->empty('None Selected'),
            ]))
                ->title('Deposit Funds')
                ->applyButton('Deposit Funds'),

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
                TD::make('status', 'Approval Status')->render(function ($data) {
                    return $data->status == 'approved' ? 'Approved' : 'Pending';
                }),
                TD::make('approved_by', 'Approved By')->render(function (Transaction $data) {
                    return $data->status == 'approved' ? optional($data->approvedBy)->name : 'Not Approved';
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
        echo "Hello World";
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function deposit(Request $request)
    {
        $institution = null;

        if ($this->currentUser()->inRole('system-admin')) {
            $institution = Institution::find($request->input('institution_id'));
        } else {
            $institution =  $this->currentUser()->institution;
        }

        $accountId = $institution->account->id;

        $amount = $request->input('amount');
        $method = $request->input('method');

        $transaction = new Transaction([
            'amount' => (int) Str::of($amount)->replace(['Ush', ','], '')->trim()->toString(),
            'method' => $method,
            'account_id' => $accountId,
            'type' => 'credit',
            'institution_id' => $institution->id,
            'deposited_by' => $request->input('deposited_by'),
            'initiated_by' => auth()->user()->id,
        ]);

        $transaction->save();

        Alert::success('Institution account has been credited with ' . $amount . ' You\'ll be notified once an accountant has approved the transaction');

        return back();
    }


    public function currentUser(): User
    {
        return auth()->user();
    }
}
