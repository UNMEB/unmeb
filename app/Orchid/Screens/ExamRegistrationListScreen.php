<?php

namespace App\Orchid\Screens;

use App\Exports\ExamRegistrationExport;
use App\Models\Course;
use App\Models\Institution;
use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Orchid\Layouts\ExportExamRegistrationForm;
use DB;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamRegistrationListScreen extends Screen
{
    public $period;
    public $activePeriod;
    public $filters = [];

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $this->filters = $request->get('filter');

        $queryPeriod = $request->query('period');

        if (is_null($queryPeriod)) {
            $this->activePeriod = RegistrationPeriod::whereFlag(1, true)->first()->id;
        }

        if (!is_null($queryPeriod)) {
            $this->activePeriod = $queryPeriod;
        }

        $query = Student::withoutGlobalScopes()
            ->select(
                'r.id as registration_id',
                'i.id as institution_id',
                'i.institution_name',
                'c.id as course_id',
                'c.course_name',
                'year_of_study as semester',
                'reg_start_date as start_date',
                'reg_end_date as end_date'
            )
            ->from('students AS s')
            ->join('student_registrations As sr', 'sr.student_id', '=', 's.id')
            ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
            ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->join('institutions AS i', 'i.id', '=', 'r.institution_id')
            ->join('courses AS c', 'c.id', '=', 'r.course_id')
            ->groupBy('i.institution_name', 'i.id', 'c.course_name', 'c.id', 'registration_id');

        $query->where('rp.id', $this->activePeriod);

        $query->orderBy('institution_name', 'asc');
        $query->orderBy('course_name', 'desc');
        $query->orderBy('semester', 'asc');

        if (auth()->user()->inRole('institution')) {
            $query->where('r.institution_id', auth()->user()->institution_id);
        }

        $query->where('rp.id', $this->activePeriod);

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
            'registrations' => $query->paginate(10),
        ];

    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        if (!is_null($this->activePeriod)) {
            $period = RegistrationPeriod::select('*')
                ->where('id', $this->activePeriod)
                ->first();

            return 'Exam registrations for ' . $period->reg_start_date->format('Y-m-d') . ' / ' . $period->reg_end_date->format('Y-m-d');
        }

        return 'Exam Registrations';
    }

    public function description(): ?string
    {
        return 'View Exam Registrations, application statuses. Filter Exam Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        // Get all NSIN Registration Periods
        $periods = RegistrationPeriod::select('id', 'reg_start_date', 'reg_end_date')
            ->orderBy('id', 'desc')
            ->get();

        $layouts = $periods->map(function ($period) {
            return Link::make("#$period->id " . $period->reg_start_date->format('Y-m-d') . ' / ' . $period->reg_end_date->format('Y-m-d'))
                ->route('platform.registration.exam.registrations.list', [
                    'period' => $period->id,
                ]);
        });

        return [
            ModalToggle::make('Export Exam Registrations')
                ->modal('exportExamRegistrations')
                ->modalTitle('Export Exam Registrations')
                ->method('exportExams'),

            DropDown::make('Change Period')
                ->icon('bs.arrow-down')
                ->list($layouts->toArray())
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::modal('exportExamRegistrations', ExportExamRegistrationForm::class)
                ->rawClick()
                ->open(false),
            Layout::rows([
                Group::make([
                    Relation::make('institution_id')
                        ->title('Select Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->canSee(!auth()->user()->inRole('institution'))
                        ->chunk(20),

                    Relation::make('course_id')
                        ->title('Select Institution')
                        ->fromModel(Course::class, 'course_name')
                        ->canSee(!auth()->user()->inRole('institution'))
                        ->chunk(20),
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
            Layout::table('registrations', [
                TD::make('#')->render(fn($model, object $loop) => $loop->index + 1),
                TD::make('institution_name', 'Institution')->canSee(!auth()->user()->inRole('institution')),
                TD::make('course_name', 'Program'),
                TD::make('semester', 'Semester'),
                TD::make('start_date', 'Start Date'),
                TD::make('end_date', 'End Date'),
                TD::make('pending', 'Pending')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->registration_id,
                        'sr_flag' => 0
                    ])->count('id');
                }),
                TD::make('approved', 'Approved')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->registration_id,
                        'sr_flag' => 1
                    ])->count('id');
                }),
                TD::make('rejected', 'Rejected')->render(function ($data) {
                    return StudentRegistration::where([
                        'registration_id' => $data->registration_id,
                        'sr_flag' => 2
                    ])->count('id');
                }),
                TD::make('actions', 'Actions')->render(
                    fn($data) => Link::make('Details')
                        ->class('btn btn-primary btn-sm link-primary')
                        ->route('platform.registration.exam.registrations.details', [
                            'registration_period_id' => $this->activePeriod,
                            'registration_id' => $data->registration_id,
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,
                        ])
                )
            ])
        ];
    }

    public function filter(Request $request)
    {
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');

        $filterParams = [];

        if (!empty($institutionId)) {
            $filterParams['filter[institution_id]'] = $institutionId;
        }

        if (!empty($courseId)) {
            $filterParams['filter[course_id]'] = $courseId;
        }

        $url = route('platform.registration.exam.registrations.list', $filterParams);

        return redirect()->to($url);
    }

    public function exportExams(Request $request)
    {
        $examRegistrationPeriodId = $request->input('exam_registration_period_id');
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $semester = $request->input('year_of_study');
        $examStatus = $request->input('exam_status');

        $students = Student::withoutGlobalScopes()->
            select([
                's.id as id',
                's.surname',
                's.firstname',
                's.othername',
                's.gender',
                's.dob',
                'd.district_name as district',
                'c.nicename as country',
                's.nsin as nsin',
                's.telephone',
                'sr.trial',
                'sr.course_codes',
                'sr.no_of_papers'
            ])
            ->from('students AS s')
            ->join('student_registrations as sr', 'sr.student_id', '=', 's.id')
            ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->leftJoin('countries AS c', 'c.id', '=', 's.country_id')
            ->leftJoin('districts as d', 'd.id', '=', 's.district_id')
            ->where('r.institution_id', $institutionId)
            ->where('r.course_id', $courseId)
            ->where('rp.flag', 1)
            ->where('sr.sr_flag', $examStatus)
            ->where('r.year_of_study', $semester)
            ->get();

        return Excel::download(new ExamRegistrationExport($students), 'exam_registrations.xlsx');
    }
}
