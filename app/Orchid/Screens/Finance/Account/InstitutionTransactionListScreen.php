<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Models\Account;
use App\Models\Institution;
use App\Models\Transaction;
use Orchid\Screen\Screen;

class InstitutionTransactionListScreen extends Screen
{

    /**
     * @var Account
     */
    public $account;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Account $account): iterable
    {
        $account->load(['transactions']);
        return [
            'account' => $account,
            'transactions' => Transaction::where('account_id', $account->id)->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Institution Transactions';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
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
        return [];
    }
}
