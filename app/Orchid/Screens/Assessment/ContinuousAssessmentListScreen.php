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
        return [];
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

    public function submitMarks(Request $request)
    {

        $registrationPeriodId = $request->get('exam_registration_period_id');
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');
        $paperId = $request->get('paper_id');
        $paperType = $request->get('paper_type');
        $students = $request->get('students');

        foreach ($students as $student) {
            // Create a new assessment
            $assessment = new ContinuousAssessment();
            $assessment->registration_period_id = $registrationPeriodId;
            $assessment->institution_id = $institutionId;
            $assessment->course_id = $courseId;
            $assessment->paper_id = $paperId;
            $assessment->student_id = $student['id'] ?? $student['student_id']; // Ensure you're getting the correct ID
            $assessment->paper_type = $paperType;
            $assessment->created_by = auth()->id();

            if ($paperType == 'Theory') {
                // Theory
                $assignmentMarks = ($student['first_assessment_marks'] + $student['second_assessment_marks']) / 2;
                $testMarks = ($student['first_test_marks'] + $student['second_test_marks']) / 2;

                $assessment->theory_marks = [
                    'first_assessment_marks' => $student['first_assessment_marks'],
                    'second_assessment_marks' => $student['second_assessment_marks'],
                    'first_test_marks' => $student['first_test_marks'],
                    'second_test_marks' => $student['second_test_marks'],
                ];
                // Use the calculateTotalCAMarkTheory method to calculate the total theory marks
                $assessment->total_marks = $assessment->calculateTotalCAMarkTheory($assignmentMarks, $testMarks);
            } else {
                // Practical
                $practicalMark = $student['practical_assessment_marks'];
                $clinicalMark = $student['practical_clinical_assessment_marksassessment_marks'];
                $logbookMark = $student['logbook_assessment_marks'];

                $assessment->practical_assessment_markss = [
                    'practical_assessment_marks' => $practicalMark,
                    'practical_clinical_assessment_marksassessment_marks' => $clinicalMark,
                    'logbook_assessment_marks' => $logbookMark,
                ];
                // Use the calculateTotalCAMarkPractical method to calculate the total practical marks
                $assessment->total_marks = $assessment->calculateTotalCAMarkPractical($practicalMark, $clinicalMark, $logbookMark);
            }

            $assessment->save();
        }

        Alert::info('Successfully added marks.');

        return back();
    }
}
