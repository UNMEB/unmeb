<?php

namespace App\Orchid\Screens;

use App\Models\NsinRegistration;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class NSINRejectedScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = NsinRegistration::query()
            ->select(
                'nsin_registrations.*',
                'institutions.name AS institution_name',
                'courses.name AS course_name',
                'years.name AS year_name',
                DB::raw('IFNULL(rejected_females, 0) AS rejected_females'),
                DB::raw('IFNULL(rejected_males, 0) AS rejected_males'),
                DB::raw('IFNULL(registered_females, 0) AS registered_females'),
                DB::raw('IFNULL(registered_males, 0) AS registered_males')
            )
            ->join('institutions', 'nsin_registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
            ->join('years', 'nsin_registrations.year_id', '=', 'years.id')
            ->leftJoinSub(function ($query) {
                $query->select(
                    'nsin_registration_id',
                    DB::raw('SUM(CASE WHEN s.gender = "Female" AND student_registration_nsins.verify = 0 THEN 1 ELSE 0 END) AS rejected_females'),
                    DB::raw('SUM(CASE WHEN s.gender = "Male" AND student_registration_nsins.verify = 0 THEN 1 ELSE 0 END) AS rejected_males'),
                    DB::raw('SUM(CASE WHEN s.gender = "Female" THEN 1 ELSE 0 END) AS registered_females'),
                    DB::raw('SUM(CASE WHEN s.gender = "Male" THEN 1 ELSE 0 END) AS registered_males')
                )
                    ->from('student_registration_nsins')
                    ->join('students AS s', 'student_registration_nsins.student_id', '=', 's.id')
                    ->groupBy('nsin_registration_id');
            }, 'srn', 'nsin_registrations.id', '=', 'srn.nsin_registration_id')
            ->where('nsin_registrations.completed', 1)
            ->where('nsin_registrations.nsin_verify', 0);


        return [
            'records' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Rejected NSIN Student Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::table('records', [
                TD::make('id', 'ID'),
                TD::make('institution', 'Institution')->render(fn ($row) => optional($row->institution)->name),
                TD::make('course', 'Course')->render(fn ($row) => optional($row->course)->name),
                TD::make('month', 'Month'),
                TD::make('year', 'Year')->render(fn ($row) => optional($row->year)->name),
                TD::make('rejected_males', 'Rejected Males'),
                TD::make('rejected_females', 'Rejected Females'),
                TD::make('registered_males', 'Registered Males'),
                TD::make('registered_females', 'Registered Females'),
            ])
        ];
    }
}
