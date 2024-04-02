<?php

namespace App\Orchid\Screens;

use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Orchid\Layouts\ApplyForExamsForm;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class ExamApplicationListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $activeExamPeriod = RegistrationPeriod::whereFlag(1, true)->first();

        $pendingQuery = Student::query()
            ->from('students as s')
            ->join('institutions as i', 'i.id', 's.institution_id')
            ->paginate();

        $approvedQuery = Student::query()
            ->from('students as s')
            ->join('institutions as i', 'i.id', 's.institution_id')
            ->paginate();


        return [
            'pending_students' => $pendingQuery,
            'approved_students' => $approvedQuery,
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
                ->modalTitle('New Exam Applications')
                ->class('btn btn-success')
                ->method('applyForExams')
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

            Layout::tabs([
                'Pending NSINs (Current Period)' => Layout::table('pending_students', [
                    TD::make('id', 'ID'),
                    TD::make('fullName', 'Name'),
                    TD::make('gender', 'Gender'),
                    TD::make('dob', 'Date of Birth'),
                    TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
                    TD::make('district_id', 'District')->render(fn(Student $student) => optional($student->district)->district_name),
                    TD::make('identifier', 'Identifier')->render(fn(Student $student) => $student->identifier),
                    TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin == null ? 'NOT APPROVED' : $student->nsin),
                ]),
                'Approved NSINs (Current Period)' => Layout::table('approved_students', [
                    TD::make('id', 'ID'),
                    TD::make('fullName', 'Name'),
                    TD::make('gender', 'Gender'),
                    TD::make('dob', 'Date of Birth'),
                    TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
                    TD::make('district_id', 'District')->render(fn(Student $student) => optional($student->district)->district_name),
                    TD::make('identifier', 'Identifier')->render(fn(Student $student) => $student->identifier),
                    TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin == null ? 'NOT APPROVED' : $student->nsin),
                ]),
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
