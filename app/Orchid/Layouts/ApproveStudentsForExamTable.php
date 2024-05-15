<?php

namespace App\Orchid\Layouts;

use App\Models\Student;
use App\Orchid\Screens\FormTable;
use App\Orchid\Screens\TDCheckbox;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ApproveStudentsForExamTable extends FormTable
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
            TD::make('fullName', 'Name'),
            TD::make('gender', 'Gender'),
            TD::make('dob', 'Date of Birth'),
            TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
            TD::make('district_id', 'District')->render(fn(Student $student) => $student->district->district_name),
            TD::make('nsin', 'NSIN'),
            TD::make('telephone', 'Phone Number'),
            TD::make('sr_flag', 'Status')->render(fn($student) => $student->sr_flag == 0 ? 'PENDING' : ($student->sr_flag == 1 ? 'ACTIVE' : 'REJECTED')),
            TDCheckbox::make('approve_students.', 'Approve')
                ->columnKey('approve_students'),
            TDCheckbox::make('reject_students.', 'Reject')
                ->columnKey('reject_students'),
            TD::make('rejection_reason', 'Rejection Reason')->render(fn($student) => Input::make('reject_reasons[' . $student->id . ']'))
        ];
    }

    protected function striped(): bool
    {
        return false;
    }

    protected function compact(): bool
    {
        return true;
    }

    protected function bordered(): bool
    {
        return true;
    }

    protected function hoverable(): bool
    {
        return true;
    }
}
