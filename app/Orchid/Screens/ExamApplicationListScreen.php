<?php

namespace App\Orchid\Screens;

use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Orchid\Layouts\ApplyForExamsForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamApplicationListScreen extends Screen
{
    public $filters = [];

    public $registrationId;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $activePeriod = RegistrationPeriod::query()
        ->where('flag', 1)
        ->first();
    
        $query = StudentRegistration::query()
            ->from('student_registrations as sr')
            ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
            ->join('students as s', 'sr.student_id', '=', 's.id')
            ->join('institutions AS i', 'r.institution_id', '=', 'i.id')
            ->join('courses AS c', 'c.id', '=', 'r.course_id')
            ->join('registration_periods AS rp', 'r.registration_period_id', '=', 'rp.id')
            ->select([
                'r.id as registration_id',
                'i.institution_name',
                'c.course_name',
                'r.year_of_study as semester',
                'rp.reg_start_date as start_date',
                'rp.reg_end_date as end_date',
                'rp.academic_year',
            ])
            ->groupBy('i.institution_name', 'c.course_name', 'r.id');
        
        if (auth()->user()->inRole('institution')) {
            $query->where('r.institution_id', auth()->user()->institution_id);
        }

        $query->where('rp.id', $activePeriod->id);
        
        // Debugging
        // dd($query->toSql(), $query->getBindings());
        
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
            ->method('exportExamApplications')
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
