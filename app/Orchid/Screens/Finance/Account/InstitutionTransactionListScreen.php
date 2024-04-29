<?php

namespace App\Orchid\Screens\Finance\Account;

use App\Models\Account;
use App\Models\Institution;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

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
        return [
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
                // TD::make('print_receipt', 'Receipt')->render(function (Transaction $data) {
                //     return Button::make('Print Receipt')
                //         ->method('print', [
                //             'id' => $data->id
                //         ])
                //         ->disabled($data->status != 'approved')
                //         ->class('btn btn-sm btn-success')
                //         ->rawClick(false);
                // })

                TD::make('actions', 'Actions')
                ->render(fn (Transaction $data) =>
                     DropDown::make()
                    ->icon('bs.three-dots-vertical')
                    ->list([

                        Button::make(__('Print Receipt'))
                            ->icon('bs.receipt')
                            ->confirm(__('Confirm Action to print receipt for ' . $data->institution->institution_name))
                            ->method('print', [
                                'id' => $data->id,
                            ]),

                        Button::make(__('Rollback Transaction'))
                            ->icon('bs.trash3')
                            ->confirm(__('This transaction will be rolled back to initial state of pending.'))
                            ->method('rollback', [
                                'id' => $data->id,
                            ])
                            ->class('btn link-danger')
                            ->canSee(auth()->user()->inRole('accountant') || auth()->user()->inRole('administrator')),

                        Button::make(__('Delete Transaction'))
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
}
