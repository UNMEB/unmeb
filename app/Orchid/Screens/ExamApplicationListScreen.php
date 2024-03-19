<?php

namespace App\Orchid\Screens;

use App\Orchid\Layouts\ApplyForExamsForm;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
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
        return [];
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
                ->applyButton('Register for Exams')
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
