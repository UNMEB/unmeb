<?php

namespace App\Orchid\Screens;

use App\Models\ContinuousAssessment;
use App\Models\Course;
use App\Models\Institution;
use App\Models\Paper;
use App\Models\Student;
use App\View\Components\AddStudentMarksTable;
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
    public $paperType;

    public $institutionId;
    public $yearOfStudy;
    public $courseId;
    public $paperId;
    public $registrationPeriodId;

    public function __construct(Request $request)
    {
        $this->yearOfStudy = request()->get('year_of_study');
        $this->institutionId = request()->get('institution_id');
        $this->courseId = request()->get('course_id');
        $this->paperId = request()->get('paper_id');
        $this->paperType = request()->get('paper_type');
        $this->registrationPeriodId = request()->get('exam_registration_period_id');
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
            ->get()
            ->map(function (Student $student) {
                $continuosAssessment = ContinuousAssessment::query()
                    ->where('registration_period_id', $this->registrationPeriodId)
                    ->where('institution_id', $this->institutionId)
                    ->where('course_id', $this->courseId)
                    ->where('paper_id', $this->paperId)
                    ->where('student_id', $student->student_id)
                    ->first();
                if($continuosAssessment) {
                    if ($this->paperType == 'Theory') {
                        return (object) [
                            'id' => $student->student_id,
                            'name' => $student->full_name,
                            'first_test_marks' => optional($continuosAssessment->theory_marks['first_test_marks']) ?? null,
                            'second_test_marks' => optional($continuosAssessment->theory_marks['second_test_marks']) ?? null,
                            'first_assignment_marks' => optional($continuosAssessment->theory_marks['first_assignment_marks']) ?? null,
                            'second_assignment_marks' => optional($continuosAssessment->theory_marks['second_assignment_marks']) ?? null,
                        ];
                    } else {
                        return (object)[
                            'id' => $student->student_id,
                            'name' => $student->full_name,
                            'logbook_assessment_marks' => optional($continuosAssessment->practical_marks['logbook_assessment_marks']) ?? null,
                            'clinical_assessment_marks' => optional($continuosAssessment->practical_marks['clinical_assessment_marks']) ?? null,
                            'practical_assessment_marks' => optional($continuosAssessment->practical_marks['practical_assessment_marks']) ?? null,
    
                        ];;
                    }
                } else {
                    if ($this->paperType == 'Theory') {
                        return (object) [
                            'id'=> $student->student_id,
                            'name'=> $student->full_name,
                            'first_assignment_marks' => null,
                            'second_assignment_marks' => null,
                            'first_test_marks' => null,
                            'second_test_marks' => null,
                        ];
                    }
                    return (object)[
                        'id'=> $student->student_id,
                        'name'=> $student->full_name,
                        'logbook_assessment_marks' => null,
                        'clinical_assessment_marks' => null,
                        'practical_assessment_marks' => null,
                    ];
                }
            });

        return [
            'students' => collect($students),
            'paper_type' => $this->paperType 
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        $institution = optional(Institution::find($this->institutionId))->institution_name;
        $course = Course::find($this->courseId)->course_name;
        $paper = Paper::find($this->paperId)->paper_name;

        return "$course ($paper)";
    }

    public function description(): ?string
    {
        $institution = optional(Institution::find($this->institutionId))->institution_name;
        $course = Course::find($this->courseId)->course_name;
        $paper = Paper::find($this->paperId)->paper_name;

        return "Enter Continuous Assessment marks for $institution";
        
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
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
        return [
            Layout::component(AddStudentMarksTable::class)
        ];
    }

    public function submitMarks(Request $request)
    {
        $registrationPeriodId = $request->get('exam_registration_period_id');
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');
        $paperId = $request->get('paper_id');
        $paperType = $request->get('paper_type');
        $marks = $request->get('marks');
        
        foreach ($marks as $student => $mark) {
            // // Check for an existing 
            $assessment = ContinuousAssessment::firstOrNew([
                'registration_period_id' => $registrationPeriodId,
                'institution_id' => $institutionId,
                'course_id' => $courseId,
                'paper_id' => $paperId,
                'student_id' => $student,
            ]);

            $assessment->created_by = auth()->id();

            // Set marks based on paper type
            $this->setMarks($assessment, $mark, $paperType);

            $assessment->save();
        }

        Alert::info('Successfully added or updated marks.');

        return redirect()->route('platform.assessment.list');
    }


    private function setMarks($assessment, $mark, $paperType)
    {
        if ($paperType == 'Theory') {
            $assignmentMarks = ($mark['first_assignment_marks'] + $mark['second_assignment_marks']) / 2;
            $testMarks = ($mark['first_test_marks'] + $mark['second_test_marks']) / 2;

            $assessment->theory_marks = [
                'first_assignment_marks' => $mark['first_assignment_marks'],
                'second_assignment_marks' => $mark['second_assignment_marks'],
                'first_test_marks' => $mark['first_test_marks'],
                'second_test_marks' => $mark['second_test_marks'],
            ];

            $assessment->total_marks = $this->calculateTotalCAMarkTheory($assignmentMarks, $testMarks);
        } else {
            $practicalMark = $mark['practical_assessment_marks'];
            $clinicalMark = $mark['clinical_assessment_marks'];
            $logbookMark = $mark['logbook_assessment_marks'];

            $assessment->practical_marks = [
                'practical_assessment_marks' => $practicalMark,
                'clinical_assessment_marks' => $clinicalMark,
                'logbook_assessment_marks' => $logbookMark,
            ];

            $assessment->total_marks = $this->calculateTotalCAMarkPractical($practicalMark, $clinicalMark, $logbookMark);
        }
    }

    public function calculateTotalCAMarkPractical($practicalTest, $clinicalPractice, $logBook)
    {
        return $practicalTest + $clinicalPractice + $logBook;
    }

    public function calculateTotalCAMarkTheory($assignmentMarks, $testMarks)
    {
        return $assignmentMarks + $testMarks;
    }
}
