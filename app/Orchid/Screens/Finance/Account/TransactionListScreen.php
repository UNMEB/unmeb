<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Models\Account;
use App\Models\Institution;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use NumberFormatter;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\DateTimer;
use Orchid\Screen\Fields\Group;
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
        $transactions = Transaction::with('institution', 'account')->whereIn('status', ['approved', 'flagged'])->latest();
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
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Deposit Funds')
                ->modal('depositFundsModal')
                ->method('deposit')
                ->icon('wallet')
                ->class('btn btn-sm btn-success link-success'),

            ModalToggle::make('Generate Statement')
                ->modal('createStatementModal')
                ->method('createStatement')
                ->icon('archive')
                ->rawClick()
                ->class('btn btn-sm btn-primary'),

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
                        'agent_banking' => 'Agent Banking'
                    ])
                    ->empty('None Selected'),
            ]))
                ->title('Deposit Funds')
                ->applyButton('Deposit Funds'),

            Layout::modal('createStatementModal', Layout::rows([
                Relation::make('institution_id')
                    ->fromModel(Institution::class, 'institution_name')
                    ->chunk(20)
                    ->title('Select Institution')
                    ->placeholder('Select an institution')
                    ->applyScope('userInstitutions')
                    ->value(auth()->user()->institution_id),

                DateTimer::make('start_date')
                    ->title('Start date')
                    ->allowInput()
                    ->required()
                    ->format('Y-m-d'),

                DateTimer::make('end_date')
                    ->title('End date')
                    ->allowInput()
                    ->required()
                    ->format('Y-m-d'),


            ]))->title('Generate Statement')
                ->applyButton('Generate Statement')
                ->rawClick(),

            Layout::rows([
                Group::make([
                    Relation::make('institution_id')
                        ->title('Select Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->chunk(20),

                    // Filter By Transaction Type
                    Select::make('transaction_type')
                        ->title('Filter By Transaction Type')
                        ->options([
                            'credit' => 'Credit',
                            'debit' => 'Debit',
                        ])
                        ->empty('Select Option'),

                    // Filter By Transaction Method
                    Select::make('transaction_method')
                        ->title('Filter By Transaction Method')
                        ->options([
                            'bank' => 'Bank Transfer',
                            'agent_banking' => 'Agent Banking',
                        ])
                        ->empty('Select Option'),

                ]),

                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ]),

            Layout::table('transactions', [
                TD::make('id', 'Transaction ID'),
                TD::make('account_id', 'Institution')->render(function (Transaction $data) {
                    return $data->institution->institution_name;
                })->canSee($this->currentUser()->inRole('system-admin')),
                TD::make('type', 'Transaction Type')->render(function ($data) {
                    return $data->type == 'credit' ? 'Account Credit' : 'Account Debit';
                }),
                TD::make('method', 'Transaction Method')->render(function ($data) {
                    return $data->method == 'bank' ? 'Bank Transfer/Payment' : 'Agent Banking';
                }),
                TD::make('amount', 'Amount')->render(function ($data) {
                    return 'Ush ' . number_format($data->amount);
                }),
                TD::make('status', 'Approval Status')->render(function ($data) {
                    $status = Str::upper($data->status);
                    return $status;
                }),
                TD::make('approved_by', 'Approved By')->render(function (Transaction $data) {
                    if ($data->approvedBy == null) {
                        return "SYSTEM";
                    }

                    return $data->status == 'approved' ? optional($data->approvedBy)->name : 'Not Approved';
                }),
                TD::make('created_at', 'Transaction Date')
                    ->usingComponent(DateTimeSplit::class),
                TD::make('updated_at', 'Updated At')
                    ->usingComponent(DateTimeSplit::class),
                TD::make('print_receipt', 'Receipt')->render(function (Transaction $data) {
                    return Button::make('Print Receipt')
                        ->method('print', [
                            'id' => $data->id
                        ])
                        ->disabled($data->status != 'approved')
                        ->class('btn btn-sm btn-success')
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

        $settings = \Config::get('settings');

        $receiptData = [
            'amount' => 'Ush ' . number_format($amount),
            'amountInWords' => Str::title($amountInWords),
            'address' => $address,
            'approvedBy' => $transaction->approvedBy->name ?? 'UNMEB OSRS',
            'institution' => $transaction->institution->institution_name,
            'finance_signature' => $settings['signature.finance_signature'] ?? '',
            'status' => $transaction->status,
            'date' => $transaction->updated_at,

        ];

        $pdf = Pdf::loadView('receipt', $receiptData);

        return $pdf->stream('receipt.pdf');
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
            $institution = $this->currentUser()->institution;
        }

        $accountId = $institution->account->id;

        $amount = $request->input('amount');
        $method = $request->input('method');
        $remoteTxId = $request->input('transaction_id');

        $transaction = new Transaction([
            'amount' => (int) Str::of($amount)->replace(['Ush', ','], '')->trim()->toString(),
            'method' => $method,
            'account_id' => $accountId,
            'type' => 'credit',
            'institution_id' => $institution->id,
            'deposited_by' => $request->input('deposited_by'),
            'initiated_by' => auth()->user()->id,
            'transaction_id' => $remoteTxId,
        ]);

        $transaction->save();

        // Alert::success('Institution account has been credited with ' . $amount . ' You\'ll be notified once an accountant has approved the transaction');

        \RealRashid\SweetAlert\Facades\Alert::success('Action Completed', 'Institution account has been credited with ' . $amount . ' You\'ll be notified once an accountant has approved the transaction');

        return back();
    }


    public function currentUser(): User
    {
        return auth()->user();
    }

    public function createStatement(Request $request)
    {
        $startDate = Carbon::createFromFormat('Y-m-d', $request->get('start_date'))->startOfDay();
        $endDate = Carbon::createFromFormat('Y-m-d', $request->get('end_date'))->endOfDay();
        $institutionId = $request->get('institution_id');
        $account = Account::where('institution_id', '=', $institutionId)->first();

        if (!$account) {
            return \RealRashid\SweetAlert\Facades\Alert::error('Account Not Found', 'Unable to find this account');
        }

        // // Fetch transactions within the provided date range
        // $query = Transaction::where('account_id', $account->id)
        //     ->whereBetween('created_at', [$startDate, $endDate])
        //     ->orderBy('created_at', 'desc');

        // $pendingTransactions = clone $query; // Clone the query instance
        // $pendingTransactions = $pendingTransactions->where('type', 'credit')
        //     ->where('status', 'pending')
        //     ->sum('amount');

        // $approvedTransactions = Transaction::where('account_id', $account->id)
        //     ->whereBetween('created_at', [$startDate, $endDate])
        //     ->where('type', 'credit')
        //     ->sum('amount');

        // $transactions = $query->get();

        // Fetch transactions within the provided date range
        $query = Transaction::where('account_id', $account->id)
            ->whereBetween('updated_at', [$startDate, $endDate]);

        $transactions = $query->orderBy('updated_at', 'asc')->get();

        $institution = Institution::where('id', $institutionId)->first();
        $iName = $institution->institution_name;
        $iLocation = $institution->institution_location;
        $iPhone = $institution->phone_no;

        // Prepare data for PDF
        $pdfData = [
            'account' => $account,
            'transactions' => $transactions,
            'pendingTransactions' => 0,
            'approvedTransactions' => 0,
            'statementDate' => now()->format('F j, Y'),
            'customerInfo' => [
                'name' => $iName,
                'location' => $iLocation,
                'phone' => $iPhone
            ],
            'pendingBalance' => 0,
            'actualBalance' => 0,
            'balance' => number_format(Account::where('id', $account->id)->sum('balance')),
            'expense' => number_format((float) Transaction::where('institution_id', $institutionId)
                ->where('type', 'debit')
                ->where('status', 'approved')
                ->sum('amount'), 0)
        ];

        // Generate PDF
        $pdf = PDF::loadView('account_statement', $pdfData);

        // Download PDF
        return $pdf->stream('account_statement.pdf');
    }

}
