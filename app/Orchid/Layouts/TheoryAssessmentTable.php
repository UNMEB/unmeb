<?php

namespace App\Orchid\Layouts;

use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class TheoryAssessmentTable extends Table
{
    /**
     * Data source.
     *
     * The name of the key to fetch it from the query.
     * The results of which will be elements of the table.
     *
     * @var string
     */
    protected $target = '';

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
            TD::make('assignment_1', 'Assignment 1'),
            TD::make('assignment_2', 'Assignment 2'),
            TD::make('test_1', 'Test 1'),
            TD::make('test_2', 'Test 2'),
            TD::make('total_assignment_mark', 'Total Assignment Mark'),
            TD::make('total_test_mark', 'Total Test Mark'),
            TD::make('total_mark', 'Total Mark'),
            // Add more columns as needed based on your model fields
        ];
    }
}
