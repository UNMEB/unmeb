<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\User;
use App\View\Components\GenderDistributionByCourseChart;
use App\View\Components\StudentRegistrationByCourseBarChart;
use DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar()
    {
        return [
            Button::make('Downalod User Manual')
                ->class('btn btn-success')
                ->rawClick(false),
        ];
    }

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query1 = StudentRegistration::join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->select('courses.course_name AS course', DB::raw('COUNT(*) as count_of_students'))
            ->groupBy('registrations.course_id')
            ->orderBy('registrations.course_id', 'asc');

        if ($this->currentUser()->inRole('institution')) {
            $query1->where('registrations.institution_id', $this->currentUser()->institution_id);
        }

        $query2 = Student::select('courses.course_name', 'students.gender', \DB::raw('COUNT(*) as gender_count'))
            ->join('nsin_student_registrations', 'students.id', '=', 'nsin_student_registrations.student_id')
            ->join('nsin_registrations', 'nsin_student_registrations.nsin_registration_id', '=', 'nsin_registrations.id')
            ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
            ->groupBy('courses.course_name', 'students.gender')
        ;


        return [
            'student_registration_by_course' => $query1->get(),
            'gender_distribution_by_course' => collect($query2->get()),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::columns([
                Layout::component(StudentRegistrationByCourseBarChart::class),
                Layout::component(GenderDistributionByCourseChart::class)
            ])
        ];
    }

    public function currentUser(): User
    {
        return auth()->user();
    }
}
