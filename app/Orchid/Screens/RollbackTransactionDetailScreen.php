<?php

namespace App\Orchid\Screens;

use App\Models\NsinRegistrationPeriod;
use App\Models\Transaction;
use App\Orchid\Layouts\RollbackTransactionTable;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;

class RollbackTransactionDetailScreen extends Screen
{

    public $account_id;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $this->account_id = $request->get("account_id");

        $transactions = Transaction::where("account_id", $this->account_id)->paginate();

        return [
            'transactions' => $transactions,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Rollback Transaction Details';
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
            RollbackTransactionTable::class
        ];
    }

    public function submit(Request $request): void
    {
        $transactionIds = $request->get('transactions');
        
        foreach ($transactionIds as $transactionId) {
            $transaction = Transaction::find($transactionId);

            // Check if comment contains exam then thats an exam transaction

            // Check if comment contains nsin then thats a nsin transaction

            // Check if comment contains logbook then thats a logbook transaction

            // Check if comment contains research then thats a research transaction

            // Find the student id from the comment
        }
    }
}
