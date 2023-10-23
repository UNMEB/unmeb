<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Models\Account;
use App\Models\Institution;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\Currency;
use Orchid\Screen\Components\Cells\DateTimeSplit;
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
        return [
            'accounts' => Account::orderBy('updated_at', 'DESC')->paginate(),
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

            Layout::table('accounts', [
                TD::make('id', 'ID'),
                TD::make('institution', 'Institution')->render(function (Account $account) {
                    return $account->institution->institution_name;
                }),
                TD::make('balance', 'Account Balance')
                    ->render(function ($account) {
                        if ($account->balance < 500000) {
                            return '<p class="text-danger bold strong">Ush ' . number_format($account->balance, 2) . '</p>';
                        }

                        return '<p class="text-success bold strong">Ush ' . number_format($account->balance, 2) . '</p>';
                    })
                    ->alignRight(),

                TD::make('future_balance', 'Pending Balance')
                ->render(function ($account) {
                    if ($account->future_balance < 500000) {
                        return '<p class="text-default bold strong">Ush ' . number_format($account->future_balance, 2) . '</p>';
                    }

                    return '<p class="text-default bold strong">Ush ' . number_format($account->future_balance, 2) . '</p>';
                })
                    ->alignRight(),

                TD::make('last_transaction', 'Last Transaction')
                    ->alignRight()
                    ->render(function (Account $account) {
                    $balance = !empty($account->lastTransaction()) ? (float)$account->lastTransaction()->amount : 0.0;
                    return '<p class="text-default bold strong">Ush ' . number_format($balance, 2) . '</p>';
                    }),

                TD::make('last_transacted_at', __('Last Transacted At'))
                    ->render(function ($account) {
                        return !empty($account->lastTransaction()) ? $account->lastTransaction()->created_at : null;
                    })
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->sort(),


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
