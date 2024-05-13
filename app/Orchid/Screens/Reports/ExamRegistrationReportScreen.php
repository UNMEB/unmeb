<?php

namespace App\Orchid\Screens\Reports;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Registration;
use App\Models\StudentRegistration;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamRegistrationReportScreen extends Screen
{
    public $filters = [];

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $this->filters = $request->get('filter');

        $query = StudentRegistration::from('student_registrations as sr')
            ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->join('institutions as i', 'i.id', '=', 'r.institution_id')
            ->join('courses as c', 'c.id', '=', 'r.course_id')
            ->join('students as s', 's.id', '=', 'sr.student_id')
            ->select(
                'i.institution_name as institution',
                'c.course_name as course',
                'r.year_of_study',
                DB::raw('COUNT(sr.student_id) as total_students'),
                DB::raw('SUM(CASE WHEN s.gender = "Male" THEN 1 ELSE 0 END) as male_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Female" THEN 1 ELSE 0 END) as female_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Male" AND sr.sr_flag = 1 THEN 1 ELSE 0 END) as male_approved_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Female" AND sr.sr_flag = 1 THEN 1 ELSE 0 END) as female_approved_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Male" AND sr.sr_flag = 2 THEN 1 ELSE 0 END) as male_rejected_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Female" AND sr.sr_flag = 2 THEN 1 ELSE 0 END) as female_rejected_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Male" AND sr.sr_flag = 0 THEN 1 ELSE 0 END) as male_pending_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Female" AND sr.sr_flag = 0 THEN 1 ELSE 0 END) as female_pending_count'),
                DB::raw('SUM(CASE WHEN sr.sr_flag = 0 THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(CASE WHEN sr.sr_flag = 1 THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN sr.sr_flag = 2 THEN 1 ELSE 0 END) as rejected_count')
            )
            ->where('rp.flag', '=', 1)
            ->groupBy('i.institution_name', 'c.course_name', 'r.year_of_study')
            ->orderBy('i.institution_name')
            ->orderBy('c.course_name');

        if (!empty($this->filters)) {
            if (isset($this->filters['institution_id']) && $this->filters['institution_id'] !== null) {
                $institutionId = $this->filters['institution_id'];
                $query->where('r.institution_id', '=', $institutionId);
            }

            if (isset($this->filters['course_id']) && $this->filters['course_id'] !== null) {
                $courseId = $this->filters['course_id'];
                $query->where('r.course_id', '=', $courseId);
            }
        }

        return [
            'report' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Exam Registration Report';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
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
            Layout::rows([
                Group::make([
                    Relation::make('institution_id')
                        ->title('Filter By Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->canSee(!auth()->user()->inRole('institution')),

                    Relation::make('course_id')
                        ->title('Filter By Program')
                        ->fromModel(Course::class, 'course_name'),

                    // Select Year of Study
                    Select::make('year_of_study')
                        ->empty('None Selected')
                        ->title('Select Year of Study')
                        ->options([
                            'Year 1 Semester 1' => 'Year 1 Semester 1',
                            'Year 1 Semester 2' => 'Year 1 Semester 2',
                            'Year 2 Semester 1' => 'Year 2 Semester 1',
                            'Year 3 Semester 1' => 'Year 3 Semester 1',
                            'Year 3 Semester 2' => 'Year 3 Semester 2',
                        ]),
                ]),
                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ]),

            Layout::table('report', [
                TD::make('institution', 'Institution'),
                TD::make('course', 'Course'),
                TD::make('year_of_study', 'Year Of Study'),
                TD::make('total_students', 'Total Students'),
                TD::make('male_count', 'Registered Males'),
                TD::make('female_count', 'Registered Females'),
                TD::make('male_pending_count', 'Pending Males'),
                TD::make('female_pending_count', 'Pending Females'),
                TD::make('male_approved_count', 'Approved Males'),
                TD::make('female_approved_count', 'Approved Females'),
                TD::make('male_rejected_count', 'Rejected Males'),
                TD::make('female_rejected_count', 'Rejected Females'),

            ])
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function filter(Request $request)
    {
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $yearOfStudy = $request->input('year_of_study');

        $filters = [];

        if (!empty($institutionId)) {
            $filters['filter[institution_id]'] = $institutionId;
        }

        if (!empty($courseId)) {
            $filters['filter[course_id]'] = $courseId;
        }

        $url = route('platform.reports.exam_registration', $filters);

        return redirect()->to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function reset(Request $request)
    {
        return redirect()->route('platform.reports.exam_registration');
    }
}
