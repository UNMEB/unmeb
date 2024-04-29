<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Exports\InstitutionAccountExport;
use App\Models\Account;
use App\Models\Institution;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Currency;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

use Illuminate\Support\Str;
use Orchid\Support\Facades\Alert;

class AccountListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $accounts = Account::filters()
            ->orderBy('balance', 'desc')
            ->orderBy('updated_at', 'DESC');

        return [
            'accounts' => $accounts->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Institution Accounts';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            Button::make('Export Accounts')
                ->method('export')
                ->icon('archive')
                ->class('btn btn-success btn-sm link-success')
                ->rawClick(false),
        ];
    }

    public function description(): string
    {
        return 'View, filter and export institution account balances';
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [

            Layout::modal('exportAccounts', Layout::rows([
                DateRange::make('date_range')
                    ->title('Filter By Date')
                    ->format('Y-m-d')
                    ->placeholder(''),
            ]))
                ->applyButton('Export Accounts'),

            Layout::rows([
                Group::make([
                    Relation::make('institution_id')
                        ->fromModel(Institution::class, 'institution_name')
                        ->chunk(20)
                        ->title('Filter By Institution')
                        ->canSee(!auth()->user()->inRole('institution'))
                        ->placeholder('Start typing...'),
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

            Layout::table('accounts', [
                TD::make('id', 'ID'),
                TD::make('institution', 'Institution')->render(function (Account $account) {
                    return $account->institution->institution_name;
                }),
                TD::make('balance', 'Account Balance')
                    ->render(function ($account) {
                        if ($account->balance < config('settings.finance.minimum_balance')) {
                            return '<p class="text-danger bold strong">Ush ' . number_format($account->balance, 2) . '</p>';
                        }

                        return '<p class="text-success bold strong">Ush ' . number_format($account->balance, 2) . '</p>';
                    })
                    ->alignRight(),

                TD::make('future_balance', 'Pending Balance')
                    ->render(function ($account) {
                        if ($account->future_balance < config('settings.finance.minimum_balance')) {
                            return '<p class="text-default bold strong">Ush ' . number_format($account->future_balance, 2) . '</p>';
                        }

                        return '<p class="text-default bold strong">Ush ' . number_format($account->future_balance, 2) . '</p>';
                    })
                    ->alignRight(),

                TD::make('last_transaction', 'Last Transaction')
                    ->alignRight()
                    ->render(function (Account $account) {
                        $balance = !empty ($account->lastTransaction()) ? (float) $account->lastTransaction()->amount : 0.0;
                        return '<p class="text-default bold strong">Ush ' . number_format($balance, 2) . '</p>';
                    }),

                TD::make('last_transacted_at', __('Last Transacted At'))
                    ->render(function ($account) {
                        return !empty ($account->lastTransaction()) ? $account->lastTransaction()->created_at : null;
                    })
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),

                TD::make('actions', __('Actions'))
                ->render(function (Account $account) {
                    return DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([
                        Link::make(__('View Transactions'))
                            ->route('platform.systems.finance.institution.transactions', $account->institution->id)
                            ->icon('bs.pencil'),
                    ]);
                })


            ])
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

        if ($this->currentUser()->inRole('administrator')) {
            $institution = Institution::find($request->input('institution_id'));
        } else {
            $institution = $this->currentUser()->institution;
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

    public function export(Request $request)
    {
        return Excel::download(new InstitutionAccountExport, 'accounts.csv', \Maatwebsite\Excel\Excel::CSV);
    }

    public function filter(Request $request)
    {
        // Retrieve data from the request
        $institutionId = $request->input('institution_id');

        // Define the filter parameters
        $filterParams = [];

        // Check and add each parameter to the filterParams array
        if (!empty ($institutionId)) {
            $filterParams['filter[institution_id]'] = $institutionId;
        }

        // Generate the URL with the filter parameters using the "institutions" route
        $url = route('platform.systems.finance.accounts', $filterParams);

        // Redirect to the generated URL
        return redirect()->to($url);
    }

    public function reset(Request $request)
    {
        return redirect()->route('platform.systems.finance.accounts');
    }
}
