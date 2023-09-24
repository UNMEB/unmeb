<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class PracticalAssessmentTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = 'practical_assessment';

    /**
     * Get the table cells to be displayed.
     *
     * @return TD[]
     */
    protected function columns(): iterable
    {
        return [
            TD::make('id', 'ID'),
            TD::make('student_id', 'Student ID'),
            TD::make('course_paper_id', 'Course Paper ID'),
            TD::make('practical_test', 'Practical Test'),
            TD::make('clinical_practice', 'Clinical Practice'),
            TD::make('logbook_assessment', 'Logbook Assessment'),
            TD::make('total_mark', 'Total Mark'),
            // Add more columns as needed based on your model fields
        ];
    }
}
