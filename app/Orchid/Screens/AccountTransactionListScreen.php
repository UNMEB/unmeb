<?php

namespace App\Orchid\Screens;

use App\Models\Account;
use Orchid\Screen\Screen;

class AccountTransactionListScreen extends Screen
{
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
            'transactions' => $account->transactions()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'AccountTransactionListScreen';
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
        return [];
    }
}
