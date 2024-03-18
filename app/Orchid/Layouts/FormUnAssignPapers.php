<?php

namespace App\Orchid\Layouts;

use App\Orchid\Screens\FormTable;
use App\Orchid\Screens\TDCheckbox;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FormUnAssignPapers extends FormTable
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'assigned_papers';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('paper_name', 'Paper Name'),
            TD::make('paper', 'Paper'),
            TD::make('abbrev', 'Abbrev'),
            TD::make('code', 'Paper Code'),
            TD::make('year_of_study', 'Year of Study'),
            TD::make('created_at', 'Created At')
                ->usingComponent(DateTimeSplit::class),
            TD::make('updated_at', 'Updated At')
                ->usingComponent(DateTimeSplit::class),
            TDCheckbox::make('unassign'),
        ];
    }
}
