<?php

namespace App\Orchid\Screens\Assessment;

use Alert;
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
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Facades\Redirect;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\TD;

class ContinuousAssessmentListScreen extends Screen
{

    public $showAddStudentMarks = false;
    public $students = [];
    public $papertType;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $this->showAddStudentMarks = request()->get('show_add_student_marks');
        $yearOfStudy = request()->get('year_of_study');
        $institutionId = request()->get('institution_id');
        $courseId = request()->get('course_id');
        $paperId = request()->get('paper_id');
        $this->papertType = request()->get('paper_type');


        if ($this->showAddStudentMarks) {

            $students = Student::query()
                ->from('students')
                ->join('student_registrations', 'students.id', '=', 'student_registrations.student_id')
                ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
                ->join('courses', 'registrations.course_id', '=', 'courses.id')
                ->join('institutions', 'registrations.institution_id', '=', 'institutions.id')
                ->join('student_paper_registration', 'student_registrations.id', '=', 'student_paper_registration.student_registration_id')
                ->join('course_paper', 'student_paper_registration.course_paper_id', '=', 'course_paper.id')
                ->join('papers', 'course_paper.paper_id', '=', 'papers.id')
                ->where('institutions.id', $institutionId)
                ->where('registrations.year_of_study', $yearOfStudy)
                ->where('courses.id', $courseId)
                ->where('papers.id', $paperId)
                ->get();

            $this->students = $students;

            return [
                'students' => $students,
            ];
        }



        return [];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {

        if ($this->showAddStudentMarks) {
            $courseId = request()->get('course_id');
            $paperId = request()->get('paper_id');

            $courseName = \App\Models\Course::find($courseId)->course_name;
            $paperName = \App\Models\Paper::find($paperId)->paper;

            return 'Add Student Marks - ' . $courseName;
        }
        return 'Continuous Assessment';
    }

    public function description(): ?string
    {
        if ($this->showAddStudentMarks) {
            $yearOfStudy = request()->get('year_of_study');
            $regPeriodId = request()->get('exam_registration_period_id');

            $registrationPeriod = RegistrationPeriod::find($regPeriodId);

            $academicYear = $registrationPeriod->academic_year;

            if ($academicYear == null) {
                $regStartDate = $registrationPeriod->reg_start_date;
                $regEndDate = $registrationPeriod->reg_end_date;
                $academicYear = $regStartDate . ' - ' . $regEndDate;
            }

            return $academicYear . ' - ' . $yearOfStudy;
        }
        return null;
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
        $theoryForm = Layout::rows([
            Matrix::make('students')
                ->columns(['Student ID' => 'student_id',
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


            Group::make([Button::make('Submit Assessment')
                    ->method('submitMarks', [
                        'show_add_student_marks' => $this->showAddStudentMarks,
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
                ->maxRows(count($this->students)), Group::make([
                Button::make('Submit Assessment')
                ->method('submitMarks', [
                    'show_add_student_marks' => $this->showAddStudentMarks,
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

        $resultLayout = Layout::columns([
            
            Layout::tabs([
                'Continuous Assessment' => Layout::table('results', [
    
                ]),
            ]),
        ]);

        $formToShow = ($this->papertType == 'Theory') ? $theoryForm : (($this->papertType == 'Practical') ? $practicalForm : $resultLayout);

        return [

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
            // Add Student Marks Layout
            Layout::modal('addStudentMarks', AddStudentMarksForm::class)
                ->size(Modal::SIZE_LG),
            $formToShow,
        ];
    }

    public function addStudentMarks(Request $request)
    {
        $examRegistrationPeriodId = $request->get('exam_registration_period_id');
        $institutionId = $request->get('institution_id');
        $yearOfStudy = $request->get('year_of_study');
        $courseId  = $request->get('course_id');
        $paperId = $request->get('paper_id');
        $papertType = $request->get('paper_type');

        // Construct URL to match 'assessment/list' with query params
        $url = route('platform.assessment.list', [
            'institution_id' => $institutionId,
            'year_of_study' => $yearOfStudy,
            'course_id' => $courseId,
            'paper_id' => $paperId,
            'paper_type' => $papertType,
            'exam_registration_period_id' => $examRegistrationPeriodId,
            'show_add_student_marks' => true
        ]);


        return Redirect::to($url);
    }

    public function submitMarks(Request $request)
    {

        $registrationPeriodId = $request->get('exam_registration_period_id');
        $institutionId = $request->get('institution_id');
        $courseId  = $request->get('course_id');
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
