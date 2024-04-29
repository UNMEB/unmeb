<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Models\Account;
use App\Models\Institution;
use Orchid\Screen\Screen;

class InstitutionTransactionListScreen extends Screen
{
    public $account;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Account $account): iterable
    {
        return [];
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
