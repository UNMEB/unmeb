<?php

namespace App\Orchid\Layouts;

use App\Models\Student;
use App\Orchid\Screens\FormTable;
use App\Orchid\Screens\TDCheckbox;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Table;
use Orchid\Screen\TD;

class ApproveStudentsNSINsTable extends FormTable
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
            // TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
            TD::make('fullName', 'Name'),
            TD::make('gender', 'Gender'),
            TD::make('dob', 'Date of Birth'),
            TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
            TD::make('district_id', 'District')->render(fn(Student $student) => $student->district->district_name),
            TD::make('telephone', 'Phone Number'),
            // TD::make('email', 'Email'),
            TD::make('Status', 'Status')->render(function ($row) {
                return $row->verify == 1 ? 'Approved' : '';
            }),
            // TD::make('remarks', 'Remarks')->render(function (Student $student) {
            //     return Select::make
            // }),
            TD::make('approval', 'Approval Actions')->render(fn(Student $student) => Group::make([
                CheckBox::make('approve_students[' . $student->id . ']')->placeholder('Approve')->sendTrueOrFalse(),
                // Input::make('approve_reasons[' . $student->id . ']')
            ]))->alignCenter(),
            // TDCheckbox::make('students_approve', 'Students'),
            // TDCheckbox::make('students_reject', 'Students'),
            TD::make('actions', 'Rejection Actions')->render(function (Student $student) {
                return Group::make([
                    CheckBox::make('reject_students[' . $student->id . ']')->placeholder('Reject')->sendTrueOrFalse(),
                    Input::make('reject_reasons[' . $student->id . ']')
                ]);
            })->align("center"),
        ];
    }

    // protected $template = 'nsin_approval_table';

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
