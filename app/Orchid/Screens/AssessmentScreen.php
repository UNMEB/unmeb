<?php

namespace App\Orchid\Screens;

use App\Models\CoursePaper;
use App\Models\PracticalAssessmentMark;
use App\Models\Student;
use App\Models\TheoryAssessmentMark;
use App\Orchid\Layouts\PracticalAssessmentTable;
use App\Orchid\Layouts\TheoryAssessmentTable;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;

class AssessmentScreen extends Screen
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
            ModalToggle::make('New Practical Assessment')
                ->modal('createPracticalAssessmentModal') // Unique identifier for the practical assessment modal
                ->method('createPracticalAssessment') // Method to handle creating practical assessments
                ->icon('plus'),

            ModalToggle::make('New Theory Assessment')
                ->modal('createTheoryAssessmentModal') // Unique identifier for the theory assessment modal
                ->method('createTheoryAssessment') // Method to handle creating theory assessments
                ->icon('plus'),
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
            Layout::modal('createPracticalAssessmentModal', Layout::rows([
                Relation::make('student_id')
                    ->fromModel(Student::class, 'id')
                    ->displayAppend('fullName')
                    ->title('Student')
            ]))
                ->title('Add Practical Assessment')
                ->applyButton('Save Practical Assessment'),
            Layout::modal('createTheoryAssessmentModal', Layout::rows([]))
                ->title('Add Theory Assessment')
                ->applyButton('Save Theory Assesment'),
            Layout::tabs([
                'Practical Assessment' => [
                    PracticalAssessmentTable::class,
                ],
                'Theory Assessment' => [
                    TheoryAssessmentTable::class
                ]
            ])
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function create(Request $request)
    {
        $request->validate([
            'student_id' => 'required',
            'assessment_type' => 'required', // Either practical or theory
            'course_paper_id' => 'required',
            'mark' => 'required|numeric|between:0,100', // Assuming marks are between 0 and 100
            'mark_type' => 'required|in:assignment_1,assignment_2,practical_test,clinical_practice,logbook_assessment',
        ]);

        $assessmentType = $request->input('assessment_type');
        $mark = $request->input('mark');
        $markType = $request->input('mark_type');

        // Define conversion factors for different mark types
        $conversionFactors = [
            'assignment_1' => 0.2,
            'assignment_2' => 0.2,
            'practical_test' => 0.1,
            'clinical_practice' => 0.1,
            'logbook_assessment' => 0.2,
        ];

        // Get the student and course paper
        $student = Student::findOrFail($request->input('student_id'));
        $coursePaper = CoursePaper::findOrFail($request->input('course_paper_id'));



        if ($assessmentType == 'practical') {

            // Check if the student already has a PracticalAssessmentMark
            $practicalMark = $student->practicalAssessmentMarks()
                ->where('course_paper_id', $coursePaper->id)
                ->first();

            if ($practicalMark) {
                // Update the specific mark type based on the provided input
                if (isset($conversionFactors[$markType])) {
                    $practicalMark->$markType = $mark * $conversionFactors[$markType];
                    $practicalMark->total_mark = $this->calculateTotalMark($practicalMark);
                    $practicalMark->save();
                }
            } else {
                // Create a new PracticalAssessmentMark with the specific mark type
                if (isset($conversionFactors[$markType])) {
                    $data = [
                        'course_paper_id' => $coursePaper->id,
                        $markType => $mark * $conversionFactors[$markType],
                    ];

                    $assessmentMark = new PracticalAssessmentMark($data);
                    $assessmentMark->total_mark = $this->calculateTotalMark($assessmentMark);
                    $student->practicalAssessmentMarks()->save($assessmentMark);
                }
            }
        } else if ($assessmentType == 'theory') {
            // Check if the student already has a TheoryAssessmentMark
            $theoryMark = $student->theoryAssessmentMarks()
                ->where('course_paper_id', $coursePaper->id)
                ->first();

            if ($theoryMark) {
                // Update the specific mark type based on the provided input
                if (isset($conversionFactors[$markType])) {
                    $theoryMark->$markType = $mark * $conversionFactors[$markType];
                    $theoryMark->total_assignment_mark = $this->calculateTotalAssignmentMark($theoryMark);
                    $theoryMark->total_test_mark = $this->calculateTotalTestMark($theoryMark);
                    $theoryMark->total_mark = $this->calculateTotalMark($theoryMark);
                    $theoryMark->save();
                }
            } else {
                // Create a new TheoryAssessmentMark with the specific mark type
                if (isset($conversionFactors[$markType])) {
                    $data = [
                        'course_paper_id' => $coursePaper->id,
                        $markType => $mark * $conversionFactors[$markType],
                    ];

                    $assessmentMark = new TheoryAssessmentMark($data);
                    $assessmentMark->total_assignment_mark = $this->calculateTotalAssignmentMark($assessmentMark);
                    $assessmentMark->total_test_mark = $this->calculateTotalTestMark($assessmentMark);
                    $assessmentMark->total_mark = $this->calculateTotalMark($assessmentMark);
                    $student->theoryAssessmentMarks()->save($assessmentMark);
                }
            }
        }
    }

    private function calculateTotalAssignmentMark($markModel)
    {
        // Assuming you have columns assignment_1 and assignment_2 in your theory assessment table
        $assignment1 = $markModel->assignment_1;
        $assignment2 = $markModel->assignment_2;

        // Define the weight for each assignment (e.g., 20% for each assignment)
        $assignmentWeight = 0.2;

        // Calculate the total assignment mark based on the weights and individual assignment marks
        $totalAssignmentMark = ($assignment1 * $assignmentWeight) + ($assignment2 * $assignmentWeight);

        // Return the calculated total assignment mark
        return $totalAssignmentMark;
    }

    private function calculateTotalMark($assessmentMark)
    {
        if ($assessmentMark instanceof PracticalAssessmentMark) {
            // Calculate the total mark for practical assessment
            // Assuming you have columns practical_test, clinical_practice, and logbook_assessment in your practical assessment table
            $practicalTest = $assessmentMark->practical_test;
            $clinicalPractice = $assessmentMark->clinical_practice;
            $logbookAssessment = $assessmentMark->logbook_assessment;

            // Define the weight for each component (e.g., 10% for practical test, 10% for clinical practice, and 20% for logbook assessment)
            $practicalTestWeight = 0.1;
            $clinicalPracticeWeight = 0.1;
            $logbookAssessmentWeight = 0.2;

            // Calculate the total mark based on the weights and individual component marks
            $totalMark = ($practicalTest * $practicalTestWeight) +
                ($clinicalPractice * $clinicalPracticeWeight) +
                ($logbookAssessment * $logbookAssessmentWeight);
        } elseif ($assessmentMark instanceof TheoryAssessmentMark) {
            // Calculate the total mark for theory assessment
            // Assuming you have columns assignment_1, assignment_2, test_1, and test_2 in your theory assessment table
            $assignment1 = $assessmentMark->assignment_1;
            $assignment2 = $assessmentMark->assignment_2;
            $test1 = $assessmentMark->test_1;
            $test2 = $assessmentMark->test_2;

            // Define the weight for each component (e.g., 20% for each assignment, and 20% for each test)
            $assignmentWeight = 0.2;
            $testWeight = 0.2;

            // Calculate the total assignment mark based on the weights and individual component marks
            $totalAssignmentMark = ($assignment1 * $assignmentWeight) + ($assignment2 * $assignmentWeight);

            // Calculate the total test mark based on the weights and individual component marks
            $totalTestMark = ($test1 * $testWeight) + ($test2 * $testWeight);

            // Calculate the overall total mark by summing the assignment and test marks
            $totalMark = $totalAssignmentMark + $totalTestMark;
        }

        // Return the calculated total mark
        return $totalMark;
    }
}
