<?php

namespace App\Orchid\Layouts;

use App\Models\Student;
use App\Orchid\Screens\FormTable;
use App\Orchid\Screens\TDCheckbox;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class NSINRegistrationTable extends Table
{
    protected $rollback = false;

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
            TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
            TD::make('fullName', 'Name'),
            TD::make('gender', 'Gender'),
            TD::make('dob', 'Date of Birth'),
            TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
            TD::make('district_id', 'District')->render(fn(Student $student) => optional($student->district)->district_name),
            TD::make('telephone', 'Phone Number'),
            TD::make('nsin','NSIN'),
            TD::make('Status', 'Status')->render(function ($row) {
                return $row->verify == 1 ? 'Approved' : '';
            }),
            TDCheckbox::make('student')
        ];
    }

    protected function compact(): bool
    {
        return true;
    }

    protected function hoverable(): bool
    {
        return true;
    }
}
