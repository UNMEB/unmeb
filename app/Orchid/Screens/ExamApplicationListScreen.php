<?php

namespace App\Orchid\Screens;

use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Orchid\Layouts\ApplyForExamsForm;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamApplicationListScreen extends Screen
{
    public $filters = [];

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $query = Student::withoutGlobalScopes()
        ->select([
            'r.id as registration_id',
            'i.id as institution_id',
            'i.institution_name',
            'c.id as course_id',
            'c.course_name as course_name',
        ])
        ->from('students AS s')
        ->join('student_registrations as sr', 's.id', '=', 'sr.student_id')
        ->join('registrations as r', 'sr.registration_id','=','r.id')
        ->join('institutions AS i', 'r.institution_id', '=', 'i.id')
        ->join('courses AS c', 'c.id', '=', 'r.course_id')
        ->groupBy('i.institution_name', 'i.id', 'c.course_name', 'c.id', 'registration_id');

        if(auth()->user()->inRole('institution')) {
            $query->where('r.institution_id', auth()->user()->institution_id);
        }

        return [
            'applications' => $query->paginate()
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
                TD::make('actions', 'Actions')->render(
                    fn($data) => Link::make('Details')
                        ->class('btn btn-primary btn-sm link-primary')
                        ->route('platform.registration.exam.applications.details', [
                            'institution_id' => $data->institution_id,
                            'course_id' => $data->course_id,
                            'nsin_registration_id' => $data->registration_id
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
