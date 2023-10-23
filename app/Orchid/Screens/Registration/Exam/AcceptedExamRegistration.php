<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\Registration;
use App\Models\StudentRegistration;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class AcceptedExamRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Registration::query()
            ->select([
                'r.id',
                'i.id as institution_id',
                'i.institution_name',
                'c.id as course_id',
                'c.course_name',
                'r.year_of_study',
                'rp.reg_start_date',
                'rp.reg_end_date',
                DB::raw("(SELECT COUNT(*) FROM student_registrations WHERE registration_id = r.id) as registered_students")
            ])
            ->from('registrations as r')
            ->join('institutions as i', 'r.institution_id', '=', 'i.id')
            ->join('courses as c', 'r.course_id', '=', 'c.id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->where('r.completed', 1)
            ->where('r.verify', 1)
            ->where('r.approved', 1);

        return [
            'registrations' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Accepted Exam Registrations';
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
            Layout::table('registrations', [
                TD::make('institution_name', 'Institution'),
                TD::make('course_name', 'Program'),
                TD::make('year_of_study', 'Year Of Study'),
                TD::make('reg_start_date', 'Registration Start Date'),
                TD::make('reg_end_date', 'Registration Start Date'),
                TD::make('registered_students', 'Students Registered'),
                TD::make('actions', 'Actions')->render(fn (Registration $data) => Link::make('Details')
                ->class('btn btn-primary btn-sm link-primary')
                ->route('platform.registration.exam.accepted.details', [
                    'institution_id' => $data->institution_id,
                    'course_id' => $data->course_id,
                    'registration_id' => $data->id
                ]))

            ])
        ];
    }
}
