<?php

namespace App\Orchid\Screens\Reports;

use App\Models\Course;
use App\Models\Institution;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class RegistrationReportScreen extends Screen
{

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        // $distinctInstitutions = Institution::query()
        //     ->select('institutions.short_name as center', 'institutions.code', 'courses.course_code', 'papers.abbrev')
        //     ->distinct()
        //     ->join('institution_course', 'institutions.id', '=', 'institution_course.institution_id')
        //     ->join('courses', 'institution_course.course_id', '=', 'courses.id')
        //     ->join('course_paper', 'courses.id', '=', 'course_paper.course_id')
        //     ->join('papers', 'course_paper.paper_id', '=', 'papers.id')
        //     ->get();

        $distinctInstitutions = Institution::query()
        ->select('institutions.short_name as center', 'institutions.code')
        ->get();

        $distinctCourses = DB::table('courses as c')
            ->join('course_paper as cp', 'c.id', '=', 'cp.course_id')
            ->join('papers as p', 'cp.paper_id', '=', 'p.id')
            ->select('c.id', 'c.course_code')
            ->distinct()
            ->where('cp.flag', '=', 1)
            ->where('p.year_of_study', '=', 'Year 1 semester 1')
            ->orderBy('c.id')
            ->get();

        foreach ($distinctCourses as $distinctCourse) {
            $distinctCourseId = $distinctCourse->id;
            $papers = DB::table('papers as p')
                ->join('course_paper AS cp', 'p.id', '=', 'cp.paper_id')
                ->where('cp.flag', 1)
                ->where('p.year_of_study', 'Year 1 Semester 1')
                ->where('cp.course_id', $distinctCourseId)
                ->get();

            $courses[] = (object)[
                'count' => count($papers),
                'papers' => $papers,
                'course' => $distinctCourse
            ];
        }

        return [
            'institutions' => $distinctInstitutions,
            'courses' => $courses
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'RegistrationReportScreen';
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
            Layout::view('registration_report')
        ];
    }
}
