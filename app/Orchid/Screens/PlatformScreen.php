<?php

declare(strict_types=1);

namespace App\Orchid\Screens;

use App\Models\Account;
use App\Models\NsinRegistrationPeriod;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\Ticket;
use App\Models\Transaction;
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
     * The name of the screen displayed in the header.
     */
    public function name(): ?string
    {
        $institution = $this->currentUser()->institution;

        if ($institution) {
            return $institution->institution_name;
        }

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
    public function commandBar()
    {
        return [
            Button::make('Download User Manual')
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

        $institutionId = $this->currentUser()->institution_id;

        $tickets_count = Ticket::count();
        $open_tickets_count = Ticket::whereNull('completed_at')->count();
        $closed_tickets_count = $tickets_count - $open_tickets_count;

        $activeNsinPeriod = NsinRegistrationPeriod::whereFlag(1, true)->first();
        $activeExamPeriod = RegistrationPeriod::whereFlag(1, true)->first();

        $query1 = StudentRegistration::join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->select('courses.course_name AS course', DB::raw('COUNT(*) as count_of_students'))
            ->groupBy('registrations.course_id')
            ->orderBy('registrations.course_id', 'asc');

        if ($this->currentUser()->inRole('institution')) {
            $query1->where('registrations.institution_id', $this->currentUser()->institution_id);
        }

        $query1->where('registrations.registration_period_id', $activeExamPeriod->id);

        $query2 = StudentRegistration::join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
            ->join('courses', 'registrations.course_id', '=', 'courses.id')
            ->join('students', 'student_registrations.student_id', '=', 'students.id')
            ->select(
                'courses.course_name AS course',
                DB::raw('COUNT(*) as count_of_students'),
                DB::raw('SUM(CASE WHEN students.gender = "Male" THEN 1 ELSE 0 END) AS male_count'),
                DB::raw('SUM(CASE WHEN students.gender = "Female" THEN 1 ELSE 0 END) AS female_count')
            )
            ->groupBy('registrations.course_id')
            ->orderBy('registrations.course_id', 'asc');

        if ($this->currentUser()->inRole('institution')) {
            $query2->where('registrations.institution_id', $this->currentUser()->institution_id);
        }

        $query2->where('registrations.registration_period_id', $activeExamPeriod->id);

        // $query2 = Student::select('courses.course_name', 'students.gender', \DB::raw('COUNT(*) as gender_count'))
        //     ->join('nsin_student_registrations', 'students.id', '=', 'nsin_student_registrations.student_id')
        //     ->join('nsin_registrations', 'nsin_student_registrations.nsin_registration_id', '=', 'nsin_registrations.id')
        //     ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
        //     ->groupBy('courses.course_name', 'students.gender')
        //     ->orderBy('courses.course_name', 'asc')
        //     ->where('nsin_registrations.institution_id', $this->currentUser()->institution_id)
        //     ->where('nsin_registrations.year_id', $activeNsinPeriod->year_id);

        return [
            'student_registration_by_course' => $query1->get(),
            'gender_distribution_by_course' => collect($query2->get()),
            'metrics' => [
                'account_balance' => number_format((float) Account::where('institution_id', $institutionId)->sum('balance'), 0),
                'pending_balance' => number_format((float) Transaction::where('institution_id', $institutionId)
                    ->where('type', 'credit')
                    ->where('status', 'PENDING')
                    ->sum('amount'), 0),
                'account_expenditure' => number_format((float) Transaction::where('institution_id', $institutionId)
                    ->where('type', 'debit')
                    ->sum('amount'), 0),
                'tickets' => $tickets_count,
                'open_tickets' => $open_tickets_count,
                'closed_tickets' => $closed_tickets_count,
            ],
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]
     */
    public function layout(): iterable
    {
        $metrics = [];

        $supportMetrics = [
            'Total Support Tickets' => 'metrics.tickets',
            'Open Tickets' => 'metrics.open_tickets',
            'Closed Tickets' => 'metrics.closed_tickets',
        ];

        if ($this->currentUser()->inRole('institution')) {
            $metrics = [
                'Account Balance (UGX)' => 'metrics.account_balance',
                'Pending Balance' => 'metrics.pending_balance',
                'Total Expenditure' => 'metrics.account_expenditure',
            ];
        }

        return [
            Layout::metrics($metrics),
            Layout::metrics($supportMetrics),
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
