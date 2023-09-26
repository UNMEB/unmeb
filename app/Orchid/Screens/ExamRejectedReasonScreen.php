<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\Institution;
use App\Models\RegistrationPeriod;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamRejectedReasonScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $result = DB::table('student_registrations as sr')
        ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
        ->join('institutions as i', 'i.id', '=', 'r.institution_id')
        ->join('courses as c', 'c.id', '=', 'r.course_id')
        ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
        ->select(DB::raw('COUNT(sr.remarks) as no'), 'sr.remarks')
        ->where('sr.flag', '=', 0)
        ->where('rp.flag', '=', 1)
        ->groupBy('sr.remarks')
        ->get();

        return [
            'reasons' => []
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Exam Registration Rejection Reasons';
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
            Layout::table('reasons', [
                TD::make('id', 'ID'),
                TD::make('reason', 'reason'),
                TD::make('no', 'Number'),
            ])
        ];
    }
}
