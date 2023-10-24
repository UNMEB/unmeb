<?php

namespace App\Orchid\Screens;

use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PackingListReportScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        return [

            'report' => DB::table('student_paper_registration')
                ->join('course_paper', 'student_paper_registration.course_paper_id', '=', 'course_paper.id')
                ->join('student_registrations', 'student_paper_registration.student_registration_id', '=', 'student_registrations.id')
                ->join('courses', 'course_paper.course_id', '=', 'courses.id')
                ->join('papers', 'course_paper.paper_id', '=', 'papers.id')
                ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
                ->join('registration_periods', 'registrations.registration_period_id', '=', 'registration_periods.id')
                ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
                ->select(
                    'institutions.code AS Institution',
                    'institutions.short_name AS Center',
                    'registration_periods.id as registration_period_id',
                    'registrations.id as registration_id',
                    'registrations.year_of_study AS Year Of Study',
                    'courses.course_code AS Course',
                    'papers.abbrev AS Paper',
                    'student_registrations.trial as attempt',
                    DB::raw('COUNT(*) as registration_count'),
                    'registration_periods.reg_start_date',
                    'registration_periods.reg_end_date'
                )
                ->where('student_registrations.sr_flag', 1)
                ->groupBy(
                    'institutions.code',
                    'institutions.short_name',
                    'registration_periods.id',
                    'registrations.id',
                    'registrations.year_of_study',
                    'courses.course_code',
                    'papers.abbrev',
                    'student_registrations.trial'
                )
                ->get()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Packing List';
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
            Layout::view('packing_list')
        ];
    }
}
