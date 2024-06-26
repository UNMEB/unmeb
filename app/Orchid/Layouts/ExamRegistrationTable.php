<?php

namespace App\Orchid\Layouts;

use App\Orchid\Screens\TDCheckbox;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ExamRegistrationTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'students';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('passport', 'Photo')->render(function ($data) {
                return '<img src="' . $data->passport . '" width="50px">';
            }),
            TD::make('name', 'Student Name')->render(function ($data) {
                return $data->surname .' '. $data->othername .' '. $data->firstname;
            }),
            TD::make('gender', 'Gender'),
            TD::make('dob', 'Birth Date'),
            TD::make('telephone', 'Phone Number'),
            TD::make('nsin', 'NSIN'),
            TD::make('created_at', 'Created At')
                ->usingComponent(DateTimeSplit::class),
            TD::make('updated_at', 'Updated At')
                ->usingComponent(DateTimeSplit::class),
            TDCheckbox::make('students')  
        ];
    }
}
