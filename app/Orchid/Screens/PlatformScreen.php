<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

use Illuminate\Support\Str;
use Orchid\Screen\Actions\ModalToggle;

class PlatformScreen extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $institution = $this->currentUser()->institution;
        $accountBalance = 0;
        if ($institution) {
            $accountBalance = (float) $institution->account->balance;
        }

        return [
            'stats' => [
                'balance' => 'Ush ' . number_format($accountBalance, 2),
            ]
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return $this->currentUser()->institution()->exists() ? $this->currentUser()->institution->short_name . ' Dashboard' : 'Admin Dashboard';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return $this->currentUser()->institution()->exists()  ? 'Welcome to ' . $this->currentUser()->institution->name . ' dashboard' : 'Welcome to admin dashboard';
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
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Account Balance' => 'stats.balance'
            ])->canSee($this->currentUser()->hasAccess('platform.systems.institution.account_balance'))
        ];
    }

    public function currentUser(): User
    {
        return Auth()->user();
    }
}
