<?php

namespace App\Orchid\Screens;

use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Orchid\Layouts\ApplyForExamsForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamApplicationListScreen extends Screen
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
        $this->filters = $request->get("filter");

        $queryPeriod = $request->query('period');

        if(is_null($queryPeriod)) {
            $this->activePeriod = RegistrationPeriod::whereFlag(1, true)->first()->id;
        }

        if(!is_null($queryPeriod)) {
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

            if(auth()->user()->inRole('institution')) {
                $query->where('r.institution_id',  auth()->user()->institution_id);
            }
        
        return [
            'applications' => $query->paginate(10),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        if(!is_null($this->activePeriod)) {
            $period = RegistrationPeriod::select('*')
                    ->where('id', $this->activePeriod)
                    ->first();

            return 'Exam Applications for ' . $period->reg_start_date->format('Y-m-d') . ' / '. $period->reg_end_date->format('Y-m-d');
        }

        return 'Exam Applications';
    }

    public function description(): ?string
    {
        return 'View Exam Applications, application statuses. Filter Exam Applications';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        // Get all NSIN Registration Periods
        $periods = RegistrationPeriod::select('*')
                    ->orderBy('reg_start_date', 'desc')
                    ->get();

        $layouts = $periods->map(function ($period) {
            return Link::make($period->reg_start_date->format('Y-m-d') . ' - ' . $period->reg_end_date->format('Y-m-d'))
            ->route('platform.registration.exam.applications.list', [
                'period' => $period->id,
            ]);
        });

        return [
            ModalToggle::make('New Exam Applications')
                ->modal('newExamApplicationModal')
                ->modalTitle('Create New Exam Applications')
                ->class('btn btn-success')
                ->method('applyForExams')
                ->rawClick(false),

            ModalToggle::make('Export Exam Applications')
            ->class('btn btn-primary')
            ->modal('exportExamApplications')
            ->modalTitle('Export Exam Applications')
            ->method('exportExamApplications'),

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
            Layout::modal('newExamApplicationModal', ApplyForExamsForm::class)
                ->applyButton('Register for Exams'),

            Layout::table('applications', [
                TD::make('registration_id', 'Reg. ID'),
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
                        ->route('platform.registration.exam.applications.details', [
                            'registration_id' => $data->registration_id,
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,

                        ])
                )
            ])  
        ];
    }

    public function applyForExams(Request $request)
    {
        $institutionId = $request->get('institution_id');
        $exam_registration_period_id = $request->get('exam_registration_period_id');
        $courseId = $request->get('course_id');
        $paperIds = $request->get('paper_ids');
        $yearOfStudy = $request->get('year_of_study');
        $trial = $request->get('trial');

        $url = route('platform.registration.exam.applications.new', [
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'paper_ids' => $paperIds,
            'exam_registration_period_id' => $exam_registration_period_id,
            'year_of_study' => $yearOfStudy,
            'trial' => $trial
        ]);

        return redirect()->to($url);
    }

}
