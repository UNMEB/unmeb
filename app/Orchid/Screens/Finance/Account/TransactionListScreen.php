<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Models\Account;
use App\Models\Institution;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Models\TransactionMeta;
use App\Models\User;
use Carbon\Carbon;
use DB;
use Http;
use Illuminate\Http\Request;
use NumberFormatter;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
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
        $query = Transaction::with('institution', 'account')
            ->filters()
            ->whereIn('status', ['approved', 'flagged']);

        if (auth()->user()->inRole('accountant')) {
            $query->where('type', 'credit');
        }

        return [
            'transactions' => $query->paginate()
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

            ModalToggle::make('Offset Funds')
                ->modal('offsetFundsModal')
                ->method('offset')
                ->icon('wallet')
                ->class('btn btn-sm btn-primary link-primary')
                ->canSee(auth()->user()->inRole('administrator') || auth()->user()->inRole('accountant') || auth()->user()->inRole('administrator')),

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
                    ->canSee(!auth()->user()->inRole('institution')),

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

            Layout::modal('offsetFundsModal', Layout::rows([
                Relation::make('institution_id')
                    ->fromModel(Institution::class, 'institution_name')
                    ->chunk(20)
                    ->title('Select Institution')
                    ->placeholder('Select an institution')
                    ->applyScope('userInstitutions')
                    ->canSee($this->currentUser()->inRole('administrator') || $this->currentUser()->inRole('accountant')),

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

                Input::make('remarks')
                    ->title('Remark')
                    ->help('Reason for offsetting funds')
                    ->required(),

            ]))
                ->title('Offset Funds')
                ->applyButton('Offset Institution Funds'),

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
                })->canSee($this->currentUser()->inRole('administrator')),
                TD::make('type', 'Transaction Type')->render(function ($data) {
                    return $data->type == 'credit' ? 'Account Credit' : 'Account Debit';
                }),
                TD::make('method', 'Transaction Method')->render(function ($data) {
                    return $data->method == 'bank' ? 'Bank Transfer/Payment' : 'Agent Banking';
                }),
                TD::make('amount', 'Amount')->render(function ($data) {
                    return 'Ush ' . number_format($data->amount);
                }),
                TD::make('comment', 'Comment'),
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

                TD::make('actions', 'Actions')
                    ->render(
                        fn(Transaction $data) =>
                        DropDown::make()
                            ->icon('bs.three-dots-vertical')
                            ->list([

                                Button::make(__('Rollback'))
                                    ->method('rollback', [
                                        'id' => $data->id,
                                    ])
                                    ->class('btn link-success')
                                    ->canSee(auth()->user()->inRole('accountant') || auth()->user()->inRole('administrator')),

                                Button::make(__('Print Receipt'))
                                    ->method('print', [
                                        'id' => $data->id,
                                    ]),

                                Button::make(__('Delete'))
                                    ->icon('bs.trash3')
                                    ->confirm(__('This action can not be reversed. Are you sure you need to delete this transaction.'))
                                    ->method('remove', [
                                        'id' => $data->id,
                                    ])
                                    ->class('btn link-danger')
                                    ->canSee(auth()->user()->inRole('accountant') || auth()->user()->inRole('administrator')),
                            ])
                    ),
            ])
        ];
    }

    public function rollback(Request $request, $id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Check if transaction is older than a day
            $created_at = $transaction->created_at;
            $now = now();
            $differenceInDays = $now->diffInDays($created_at);

            if ($differenceInDays > 1) {
                \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', 'Transaction cannot be rolled back as it was created more than a day ago');
                return redirect()->back();
            }

            // Rollback transaction status to pending
            $transaction->status = 'pending';
            $transaction->save();

            // Log transaction rollback
            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'user_id' => auth()->id(),
                'action' => 'updated',
                'description' => 'Transaction status rolled back to pending',
            ]);

            // If transaction was initially approved, deduct amount from account
            if ($transaction->status === 'approved') {
                $account = Account::findOrFail($transaction->account_id);
                $account->balance -= $transaction->amount;
                $account->save();

                // Log amount deduction from account
                TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'user_id' => auth()->id(),
                    'action' => 'updated',
                    'description' => 'Amount deducted from account',
                ]);

                \RealRashid\SweetAlert\Facades\Alert::success('Action Complete', 'Transaction status has been rolled back to pending and amount has been deducted from account');
            } else {
                \RealRashid\SweetAlert\Facades\Alert::success('Action Complete', 'Transaction status has been rolled back to pending');
            }

            // Get browser and location information
            $userAgent = $request->header('User-Agent');
            $ipAddress = $request->ip();
            $browser = $this->parseUserAgent($userAgent);
            $networkMeta = $this->getNetworkMeta($ipAddress);

            $rollbackInfo = [
                'rollback_by' => auth()->user()->name,
                'rollback_at' => now()->toDateTimeString(),
                'rollback_ip' => $ipAddress,
                'rollback_browser' => $browser,
            ];

            if (!empty($networkMeta['city']) && !empty($networkMeta['region']) && !empty($networkMeta['country'])) {
                $rollbackInfo['rollback_location'] = $networkMeta['city'] . ', ' . $networkMeta['region'] . ', ' . $networkMeta['country'];
            }

            if (!empty($networkMeta['timezone'])) {
                $rollbackInfo['rollback_timezone'] = $networkMeta['timezone'];
            }

            if (!empty($networkMeta['isp'])) {
                $rollbackInfo['rollback_isp'] = $networkMeta['isp'];
            }

            if (!empty($networkMeta['org'])) {
                $rollbackInfo['rollback_org'] = $networkMeta['org'];
            }

            if (!empty($networkMeta['lat'])) {
                $rollbackInfo['rollback_lat'] = $networkMeta['lat'];
            }

            if (!empty($networkMeta['lon'])) {
                $rollbackInfo['rollback_lon'] = $networkMeta['lon'];
            }

            TransactionMeta::create([
                'transaction_id' => $transaction->id,
                'key' => 'rollback_info', // New key for rollback info
                'value' => $rollbackInfo,
            ]);

        } catch (\Throwable $th) {
            \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', 'Transaction rollback failed');
        }
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
    public function offset(Request $request)
    {
        $institution = null;

        if ($this->currentUser()->inRole('administrator') || $this->currentUser()->inRole('accountant')) {
            $institution = Institution::find($request->input('institution_id'));
        } else {
            $institution = $this->currentUser()->institution;
        }

        $account = $institution->account;

        $amount = (int) Str::of($request->input('amount'))->replace(['Ush', ','], '')->trim()->toString();
        $remarks = $request->input('remarks');

        if ($amount > $account->balance) {

            \RealRashid\SweetAlert\Facades\Alert::error('Insufficient funds', 'Unable to offset funds from this account. The current account balance is low<br /> <strong>Account Balance: Ush ' . number_format($account->balance) . '</strong>')->toHtml();

            return;
        }

        $newBalance = $account->balance - $amount;
        $account->balance = $newBalance;
        $account->save();

        // Create a new transaction
        $transaction = new Transaction([
            'amount' => (int) Str::of($amount)->replace(['Ush', ','], '')->trim()->toString(),
            'account_id' => $account->id,
            'type' => 'debit',
            'institution_id' => $institution->id,
            'initiated_by' => auth()->user()->id,
            'status' => 'approved',
        ]);

        $transaction->save();

        \RealRashid\SweetAlert\Facades\Alert::success('Account Updated', 'Institution account successfully updated. The new account balance is <br /> <strong>Account Balance: Ush ' . number_format($account->balance) . '</strong>')->toHtml();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function deposit(Request $request)
    {
        DB::beginTransaction();

        try {

            $request->validate([
                'amount' => 'required',
                'method' => 'required',
            ]);

            $institution = null;

            if ($this->currentUser()->inRole('administrator') || $this->currentUser()->inRole('accountant')) {
                $institution = Institution::find($request->input('institution_id'));
            } else {
                $institution = $this->currentUser()->institution;
            }

            if ($institution->account) {
                $accountId = $institution->account->id;
            } else {
                // Create a new account
                $account = new Account();
                $account->balance = 0;
                $account->institution_id = $institution->id;
                $account->save();

                $accountId = $account->id;
            }

            $amount = $request->input('amount');
            $method = $request->input('method');

            // Get browser and location information
            $userAgent = $request->header('User-Agent');
            $ipAddress = $request->ip();
            $browser = $this->parseUserAgent($userAgent);
            $networkMeta = $this->getNetworkMeta($ipAddress);

            $transaction = Transaction::create([
                'amount' => (int) Str::of($amount)->replace(['Ush', ','], '')->trim()->toString(),
                'method' => $method,
                'account_id' => $accountId,
                'type' => 'credit',
                'status' => 'pending',
                'institution_id' => $institution->id,
                'deposited_by' => auth()->user()->id
            ]);

            // Log the transaction
            TransactionLog::create([
                'transaction_id' => $transaction->id,
                'user_id' => auth()->user()->id,
                'action' => 'created',
                'description' => 'Transaction created',
            ]);

            $depositInfo = [
                'depositor_name' => auth()->user()->name,
                'depositor_ip' => $ipAddress,
                'deposited_at' => now()->toDateTimeString(),
                'institution_name' => $institution->institution_name,
                'browser' => $browser,
            ];

            if (!empty($networkMeta['city']) && !empty($networkMeta['region']) && !empty($networkMeta['country'])) {
                $depositInfo['depositor_location'] = $networkMeta['city'] . ', ' . $networkMeta['region'] . ', ' . $networkMeta['country'];
            }

            if (!empty($networkMeta['timezone'])) {
                $depositInfo['depositor_timezone'] = $networkMeta['timezone'];
            }

            if (!empty($networkMeta['isp'])) {
                $depositInfo['depositor_isp'] = $networkMeta['isp'];
            }

            if (!empty($networkMeta['org'])) {
                $depositInfo['depositor_org'] = $networkMeta['org'];
            }

            if (!empty($networkMeta['lat'])) {
                $depositInfo['depositor_lat'] = $networkMeta['lat'];
            }

            if (!empty($networkMeta['lon'])) {
                $depositInfo['depositor_lon'] = $networkMeta['lon'];
            }

            TransactionMeta::create([
                'transaction_id' => $transaction->id,
                'key' => 'deposit_info', // Add appropriate key for deposit info
                'value' => $depositInfo,
            ]);

            DB::commit();

            \RealRashid\SweetAlert\Facades\Alert::success('Action Completed', 'Institution account has been credited with ' . $amount . ' You\'ll be notified once an accountant has approved the transaction');

        } catch (\Throwable $th) {

            DB::rollBack();

            \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', $th->getMessage());
        }
    }

    private function parseUserAgent($userAgent)
    {
        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($userAgent);
        return $agent->browser() . ' on ' . $agent->platform();
    }

    private function getNetworkMeta($ip)
    {
        // Check if testing offline
        if ($ip === '127.0.0.1') {
            return [];
        }

        $response = Http::get('http://ip-api.com/json/' . $ip);

        if ($response->successful()) {
            $data = $response->json();

            dd($data);

            return [
                'country' => $data['country'],
                'country_code' => $data['countryCode'],
                'region' => $data['regionName'],
                'city' => $data['city'],
                'latitude' => $data['lat'],
                'longitude' => $data['lon'],
                'timezone' => $data['timezone'],
                'isp' => $data['isp'],
                'organization' => $data['org'],
                'as' => $data['as']
            ];
        }

        return [];
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
            // ->where('comment', 'NOT LIKE', 'Reversal of Exam Registration Fee for Student ID:%')
            // ->where('comment', 'NOT LIKE', 'Reversal of NSIN Registration Fee for Student ID:%')
            // ->where('comment', 'NOT LIKE', 'Reversal of Logbook Registration Fee for Student ID:%')
            // ->where('comment', 'NOT LIKE', 'Reversal of Research Guide Fee for Student ID:%')
            ->whereBetween('created_at', [$startDate, $endDate]);

        $transactions = $query->orderBy('created_at', 'asc')->get();

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

    public function filter(Request $request)
    {
        $institutionId = $request->input('institution_id');
        $transactionType = $request->input('transaction_type');
        $transactionMethod = $request->input('transaction_method');
        $dateRange = $request->input('date_range');

        // Define the filter parameters
        $filterParams = [];

        if (!empty($institutionId)) {
            $filterParams['filter[institution_id]'] = $institutionId;
        }

        $url = route('platform.systems.finance.complete', $filterParams);

        return redirect()->to($url);
    }

}
