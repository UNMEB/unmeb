<?php

namespace App\Orchid\Screens\Assessment;

use App\Models\ContinuousAssessment;
use App\Models\Institution;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Orchid\Layouts\AddStudentMarksForm;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Redirect;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\TD;

class ContinuousAssessmentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'results' => ContinuousAssessment::
            with([
                'institution',
                'course',
                'student'
            ])
            ->filters()->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Continuous Assessment';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Student Marks')
                ->icon('fa.file-signature')
                ->method('addStudentMarks')
                ->modal('addStudentMarks')
                ->modalTitle('Add Student Marks')
                ->class('btn btn-primary'),

            // Bulk Import Marks
            ModalToggle::make('Bulk Import Marks')
                ->icon('fa.file-import')
                ->method('bulkImportMarks')
                ->modal('bulkImportMarks')
                ->modalTitle('Bulk Import Marks')
                ->class('btn btn-success'),
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
            // Add Student Marks Layout
            Layout::modal('addStudentMarks', AddStudentMarksForm::class)
                ->size(Modal::SIZE_LG),

            Layout::rows([
                Group::make([
                    Relation::make('institution_name')
                        ->fromModel(Institution::class, 'institution_name')
                        ->title('Institution Name'),
                ]),
                Group::make([
                    Button::make('Submit'),
                    Button::make('Reset')->class('btn btn-dark btn-sm link-dark'),
                ])->autoWidth(),
            ]),

            Layout::columns([
                Layout::tabs([
                    'Continuous Assessment' => Layout::table('results', [
                        TD::make('id', 'ID'),
                        TD::make('institution.institution_name', 'Institution'),
                        TD::make('course.course_name','Course Name'),
                        TD::make('student.fullName','Student Name'),
                        TD::make('totalTheoryMark', 'Theory Marks'),
                        TD::make('totalPracticalMark', 'Practical Marks'),
                        TD::make('total_marks', 'Total Marks')
                    ]),
                ]),
            ])
        ];
    }

    public function addStudentMarks(Request $request)
    {
        $examRegistrationPeriodId = $request->get('exam_registration_period_id');
        $institutionId = $request->get('institution_id');
        $yearOfStudy = $request->get('year_of_study');
        $courseId = $request->get('course_id');
        $paperId = $request->get('paper_id');
        $papertType = $request->get('paper_type');

        // Construct URL to match 'assessment/list' with query params
        $url = route('platform.assessment.marks', [
            'institution_id' => $institutionId,
            'year_of_study' => $yearOfStudy,
            'course_id' => $courseId,
            'paper_id' => $paperId,
            'paper_type' => $papertType,
            'exam_registration_period_id' => $examRegistrationPeriodId,
        ]);


        return Redirect::to($url);
    }    
}
