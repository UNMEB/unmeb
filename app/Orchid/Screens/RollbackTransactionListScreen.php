<?php

namespace App\Orchid\Screens;

use App\Models\Transaction;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class RollbackTransactionListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Transaction::from('transactions as t')
        ->join('accounts as a', 't.account_id','=','a.id')
        ->join('institutions as i', 'i.id', '=', 't.institution_id')
        ->selectRaw('
            i.id as institution_id,
            i.institution_name,
            MAX(t.id) as transaction_id,
            t.account_id,
            SUM(CASE WHEN t.type = "debit" THEN t.amount ELSE 0 END) as total_debits,
            SUM(CASE WHEN t.type = "credit" THEN t.amount ELSE 0 END) as total_credits,
            a.balance as account_balance
        ')
        ->groupBy('i.id', 't.account_id', 'a.balance')
        ->orderBy('account_balance', 'desc');

        return [
            'transactions' => $query->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Rollback Transaction Activity';
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
        return [
            Layout::table('transactions', [
                TD::make('institution_name', 'Institution Name'),
                TD::make('account_balance', 'Account Balance')->render(function ($data) {
                    return number_format($data->account_balance); }),
                TD::make('total_debits', 'Debit')->render(function ($data) {
                    return number_format($data->total_debits); }),
                TD::make('total_credits', 'Credit')->render(function ($data) {
                    return number_format($data->total_credits); }),
                    TD::make('actions', 'Actions')->render(function ($data) {
                        return DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            Link::make('Rollback Action')
                            ->route('platform.systems.finance.rollback.details', [
                                'account_id' => $data->account_id,
                            ])
                        ]);
                    })
            ])
        ];
    }
}
