<?php

namespace App\Orchid\Layouts;

use App\Orchid\Screens\FormTable;
use App\Orchid\Screens\TDCheckbox;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class FormAssignPrograms extends FormTable
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'unassigned_programs';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('course_code', 'Program Code'),
            TD::make('course_name', 'Program Name'),
            TD::make('duration', 'Duration'),
            TD::make('created_at', 'Created At')
                ->usingComponent(DateTimeSplit::class),
            TD::make('updated_at', 'Updated At')
                ->usingComponent(DateTimeSplit::class),
            TDCheckbox::make('assign')
        ];
    }
}
