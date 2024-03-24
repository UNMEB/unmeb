<?php

namespace App\Orchid\Layouts;

use App\Models\Student;
use App\Orchid\Screens\FormTable;
use App\Orchid\Screens\TDCheckbox;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class RegisterStudentsForExamsTable extends FormTable
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
            // Show passport picture
            // TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
            TD::make('fullName', 'Name'),
            TD::make('gender', 'Gender'),
            TD::make('dob', 'Date of Birth'),
            TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
            TD::make('district_id', 'District')->render(fn(Student $student) => optional($student->district)->district_name),
            TD::make('identifier', 'Identifier')->render(fn(Student $student) => $student->identifier),
            TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin),
            TD::make('telephone', 'Phone Number'),
            TD::make('email', 'Email')->defaultHidden(),
            TDCheckbox::make('students', 'Students'),
        ];
    }
}
