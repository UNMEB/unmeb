<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\BiometricEnrollment;
use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinStudentRegistration;
use App\Models\Staff;
use App\Models\Student;
use App\View\Components\Chart;
use App\View\Components\GenderDistributionByCourseChart;
use App\View\Components\GenderDistributionChart;
use App\View\Components\InstitutionDistributionByCategoryChart;
use App\View\Components\InstitutionDistributionByTypeChart;
use App\View\Components\StudentRegistrationByCourseBarChart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\DateRange;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Layout;

class PlatformScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $data1 =  DB::table('student_registrations')
            ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->select('courses.course_name AS course', DB::raw('COUNT(*) as count_of_students'))
            ->groupBy('registrations.course_id')
            ->orderBy('registrations.course_id', 'asc')
            ->get();

        $genderDistributionByCourse = DB::select('
            SELECT
                c.course_name,
                s.gender,
                COUNT(*) AS gender_count
            FROM students AS s
            JOIN nsin_student_registrations AS nsr ON s.id = nsr.student_id
            JOIN nsin_registrations AS nr ON nsr.nsin_registration_id = nr.id
            JOIN courses AS c ON nr.course_id = c.id
            GROUP BY c.course_name, s.gender
        ');

        $institutionDistributionByType = DB::table('institutions')
        ->select('institution_type', DB::raw('COUNT(*) as institution_count'))
        ->groupBy('institution_type')
        ->orderByDesc('institution_count')
        ->get();

        $institutionDistributionByCategory = DB::table('institutions')
        ->select('category', DB::raw('COUNT(*) as institution_count'))
        ->groupBy('category')
        ->orderByDesc('institution_count')
        ->get();

        $pendingNsin = NsinStudentRegistration::where('verify', 0)->count();
        $verifiedNsin = NsinStudentRegistration::where('verify', 1)->count();


        

        return [
            'metrics' => [
                'institutions' => number_format(Institution::count()),
                'courses' => Course::count(),
                'students' => number_format(Student::count()),
                'staff' => number_format(Staff::count()),
                'biometric_enrollment' => BiometricEnrollment::count(),
                'pending_nsin' => number_format($pendingNsin),
                'verified_nsin' => number_format($verifiedNsin)
            ],

            'student_registration_by_course' => $data1,
            'gender_distribution_by_course' => collect($genderDistributionByCourse),
            'institution_distribution_by_type' => collect($institutionDistributionByType),
            'institution_distribution_by_category' => collect($institutionDistributionByCategory)
        ];
    }

    /**
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        return 'Uganda Nurses And Midwives Examination Board';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'View metrics, charts and various reports of Institutions, Programs, Papers, Staff, Students, and registration data.';
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
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        return [
            Layout::metrics([
                'Total Institutions' => 'metrics.institutions',
                'Total Courses' => 'metrics.courses',
                'Total Staff' => 'metrics.staff',
                'Total Students' => 'metrics.students',
                'Biometric Enrollment' => 'metrics.biometric_enrollment',
                'Pending NSIN Registrations' => 'metrics.pending_nsin',
                'Verified NSIN Registrations' => 'metrics.verified_nsin'
            ]),
            Layout::columns([

                // Student Registrations By Course
                Layout::component(StudentRegistrationByCourseBarChart::class),

                // Gender Distribution By Course
                Layout::component(GenderDistributionByCourseChart::class)
            ]),
            Layout::columns([
                // Institution Distribution By Type
                Layout::component(InstitutionDistributionByTypeChart::class),

                // Institution Distribution By Category
                Layout::component(InstitutionDistributionByCategoryChart::class)

            ])
        ];
    }
}
