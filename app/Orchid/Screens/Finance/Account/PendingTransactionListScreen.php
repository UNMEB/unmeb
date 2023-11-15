<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Models\Institution;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

use Illuminate\Support\Str;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\TextArea;

class PendingTransactionListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $transactions = Transaction::with('institution', 'account')->where('is_approved', 0)
        ->filters()
            ->defaultSort('id', 'desc')
            ->get();
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

            Layout::rows([
                Group::make([
                    // Filter By Institution
                    Input::make('institution_name')
                        ->title('Filter By Institution'),

                    // Filter By Transaction Type
                    Select::make('transaction_type')
                        ->title('Filter By Transaction Type')
                        ->options([
                            'credit' => 'Credit',
                            'debit' => 'Debit',
                        ]),

                    // Filter By Transaction Method
                    Select::make('transaction_method')
                        ->title('Filter By Transaction Method')
                        ->options([
                            'bank' => 'Bank Transfer',
                            'mobile' => 'Mobile Money',
                        ]),

                    // Filter By Date Range
                    DateRange::make('date_range')
                        ->title('Filter By Date Range')
                        ->format('Y-m-d')
                        ->placeholder('Select Date Range')
                        ->popover('Select a date range to filter transactions')
                        ->help('Filter transactions by a specific date range')
                        ->autocomplete(false)
                        ->allowClear(false)
                        ->required()
                        ->value([
                            'start' => now()->subDays(7)->format('Y-m-d'),
                            'end' => now()->format('Y-m-d'),
                        ]),

                ]),

                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ])->title('Filter Institutions'),

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
                TD::make('id', 'ID'),
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
                TD::make('actions', 'Actions')->render(function (Transaction $data) {
                    // return Button::make('Approve')->type(Color::SUCCESS)
                    //     ->method('approve', [
                    //         'id' => $data->id
                    //     ])->disabled($data->is_approved == 1)
                    //     ->canSee(auth()->user()->inRole('system-admin') || auth()->user()->inRole('accountant'));

                    return Group::make([
                        ModalToggle::make('Approve')
                            ->modal('approveTransactionModal')
                            ->modalTitle('Approve Transaction')
                            ->method('approve', [
                                'id' => $data->id
                            ])
                            ->class('btn btn-sm btn-success')
                            ->asyncParameters([
                                'transaction' => $data->id,
                            ]),
                        ModalToggle::make('Decline')
                            ->modal('declineTransactionModal')
                            ->modalTitle('Decline Transaction')
                            ->method('decline', [
                                'id' => $data->id
                            ])
                            ->asyncParameters([
                                'transaction' => $data->id,
                            ])
                            ->class('btn btn-sm btn-danger'),
                    ])->autoWidth();



                })->alignCenter()
                    ->canSee(auth()->user()->inRole('system-admin') || auth()->user()->inRole('accountant'))
            ]),

            Layout::modal('approveTransactionModal', [

                Layout::view('transaction_info', [
                    'amount' => null,
                    'institution' => null,
                    'type' => null,
                    'message' => 'Approve '
                ]),

                Layout::rows([
                    TextArea::make('comment')
                        ->title('Transaction approval remarks')
                        ->placeholder('Start typing')
                ])
            ])->async('asyncGetTransaction')
            ->applyButton('Approve Transaction'),

            Layout::modal('declineTransactionModal', [

                Layout::view('transaction_info', [
                    'amount' => null,
                    'institution' => null,
                    'type' => null,
                    'message' => 'Decline '
                ]),

                Layout::rows([
                    TextArea::make('comment')
                        ->title('Transaction decline remarks')
                        ->placeholder('Start typing')
                ])
            ])->async('asyncGetTransaction')
            ->applyButton('Decline Transaction')
        ];
    }

    public function asyncGetTransaction(Transaction $transaction): iterable
    {
        return [
            'transaction' => $transaction,
            'amount' => $transaction->amount,
            'institution' => $transaction->account->institution->institution_name,
            'type' => $transaction->type,
        ];
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


    public function approve(Request $request)
    {

        $request->validate([
            'transaction' => 'required|exists:transactions,id',
        ]);

        $id = $request->input('transaction');

        $transaction = Transaction::find($id);

        $transaction->approved_by = auth()->user()->id;
        $transaction->is_approved = 1;
        $transaction->comment = $request->input('comment');
        $transaction->save();

        // dd($transaction);

        Alert::success('Transaction has been approved and Institution account credited');

        return back();
    }

    public function decline(Request $request)
    {
        $id = $request->input('transaction');

        $transaction = Transaction::find($id);

        $transaction->approved_by = auth()->id();
        $transaction->is_approved = 0;
        $transaction->comment = $request->input('comment');
        $transaction->save();

        Alert::success('Transaction has been declined');

        return back();
    }

    public function currentUser(): User
    {
        return auth()->user();
    }
}
