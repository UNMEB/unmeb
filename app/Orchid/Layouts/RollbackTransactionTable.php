<?php

namespace App\Orchid\Layouts;

use App\Orchid\Screens\FormTable;
use App\Orchid\Screens\TDCheckbox;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class RollbackTransactionTable extends FormTable
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'transactions';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('type', 'Type'),
            TD::make('status', 'Status'),
            TD::make('comment', 'Comment'),
            TD::make('amount', 'Amount'),
            TDCheckbox::make('transaction', 'Transaction'),
        ];
    }
}
