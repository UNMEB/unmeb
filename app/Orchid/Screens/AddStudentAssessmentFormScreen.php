<?php

namespace App\Orchid\Screens;

use App\Models\ContinuousAssessment;
use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class AddStudentAssessmentFormScreen extends Screen
{
    
    public $students = [];
    public $papertType;

    public $institutionId;
    public $yearOfStudy;
    public $courseId;
    public $paperId;

    public function __construct(Request $request)
    {
        $this->yearOfStudy = request()->get('year_of_study');
        $this->institutionId = request()->get('institution_id');
        $this->courseId = request()->get('course_id');
        $this->paperId = request()->get('paper_id');
        $this->papertType = request()->get('paper_type');
    }

    
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $students = Student::query()
                ->from('students')
                ->join('student_registrations', 'students.id', '=', 'student_registrations.student_id')
                ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
                ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
                ->join('courses', 'registrations.course_id', '=', 'courses.id')
                ->join('student_paper_registration', 'student_registrations.id', '=', 'student_paper_registration.student_registration_id')
                ->join('course_paper', 'student_paper_registration.course_paper_id', '=', 'course_paper.id')
                ->join('papers', 'course_paper.paper_id', '=', 'papers.id')
                ->where('institutions.id', $this->institutionId)
                ->where('registrations.year_of_study', $this->yearOfStudy)
                ->where('courses.id', $this->courseId)
                ->where('papers.id', $this->paperId)
                ->select('students.id as student_id', 'student_registrations.id as student_registration_id', 'students.nsin', 'students.surname', 'students.firstname')
                ->get();

        // dd($this->paperId);
                
        return [
            'students' => $students
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Add Student Marks';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
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
        $theoryForm = Layout::rows([
            Matrix::make('students')
                ->columns([
                    'Student ID' => 'student_id',
                    'NSIN' => 'nsin',
                    'surname',
                    'First Name' => 'firstname',
                    'First Assessment (20%)' => 'first_assessment_marks',
                    'Second Assessment (20%)' => 'second_assessment_marks',
                    'First Test (20%)' => 'first_test_marks',
                    'Second Test (20%)' => 'second_test_marks',
                ])
                ->fields([
                    'first_assessment_marks' => Input::make()->type('number')->required()->max(20),
                    'second_assessment_marks' => Input::make()->type('number')->required()->max(20)
                        ->mask([
                            'suffix' => '%',
                        ]),
                    'first_test_marks' => Input::make()->type('number')->required()->max(20),
                    'second_test_marks' => Input::make()->type('number')->required()->max(20),
                ])
                ->removableRows(false)
                ->maxRows(count($this->students)),


            Group::make([
                Button::make('Submit Assessment')
                    ->method('submitMarks', [
                        'institution_id' => request()->get('institution_id'),
                        'year_of_study' => request()->get('year_of_study'),
                        'course_id' => request()->get('course_id'),
                        'paper_id' => request()->get('paper_id'),
                        'paper_type' => request()->get('paper_type'),
                        'exam_registration_period_id' => request()->get('exam_registration_period_id'),

                    ])
                    ->class('btn btn-primary'),
            ])->fullWidth()

        ]);

        $practicalForm = Layout::rows([
            Matrix::make('students')
                ->columns([
                    'Student ID' => 'student_id',
                    'NSIN' => 'nsin',
                    'surname',
                    'First Name' => 'firstname',
                    'Clinical Assessment (10%)' => 'clinical_assessment_marks',
                    'Practical Assessment (10%)' => 'practical_assessment_marks',
                    'Logbook Assessment (20%)' => 'logbook_assessment_marks'
                ])
                ->fields([
                    'clinical_assessment_marks' => Input::make()->type('number')->required()->max(10)
                        ->mask([
                            'suffix' => '%',
                        ]),
                    'practical_assessment_marks' => Input::make()->type('number')->required()->max(10)
                        ->mask([
                            'suffix' => '%',
                        ]),
                    'logbook_assessment_marks' => Input::make()->type('number')->required()->max(20)
                        ->mask([
                            'suffix' => '%',
                        ]),
                ])
                ->removableRows(false)
                ->maxRows(count($this->students)),
            Group::make([
                Button::make('Submit Assessment')
                    ->method('submitMarks', [
                        'institution_id' => request()->get('institution_id'),
                        'year_of_study' => request()->get('year_of_study'),
                        'course_id' => request()->get('course_id'),
                        'paper_id' => request()->get('paper_id'),
                        'paper_type' => request()->get('paper_type'),
                        'exam_registration_period_id' => request()->get('exam_registration_period_id'),

                    ])
                    ->class('btn btn-primary'),
            ])->fullWidth()

        ]);

        return [
            $this->papertType == 'Practical' ? $practicalForm : $theoryForm
        ];
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
