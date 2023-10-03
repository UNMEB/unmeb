<?php

namespace App\Orchid\Layouts;

use App\Models\Course;
use App\Models\ExamRegistrationPeriod;
use App\Models\Fee;
use App\Models\Institution;
use App\Models\Student;
use App\Models\SurchargeFee;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class RegisterExamFormListener extends Listener
{

    public $courses = [];
    public $papers = [];

    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'institution_id',
        'course_id',
        'paper_ids',
        'student_ids'
    ];

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        $registrationPeriods = ExamRegistrationPeriod::where('is_active', '=', '1')->get();

        $yearOptions = [];

        foreach ($registrationPeriods as $registrationPeriod) {
            $yearOptions[$registrationPeriod->id] = $registrationPeriod->start_date . ' - ' . $registrationPeriod->end_date;
        }

        return [
            Layout::rows([
                // Select Exam Registration Period
                Select::make('exam_registration_period_id')
                    ->options($yearOptions)
                    ->empty('None Selected')
                    ->title('Select Exam Registration Period'),

                // Select Institution
                Relation::make('institution_id')
                    ->title('Select Institution')
                    ->fromModel(Institution::class, 'name')
                    ->applyScope('forInstitution')
                    ->placeholder('Select Institution'),

                // Select Course
                Select::make('course_id')
                    ->title('Select Course')
                    ->empty('None Selected', 0)
                    ->placeholder('Select Course')
                    ->options($this->courses)
                    ->canSee(count($this->courses) > 0),

                // Select Paper
                Select::make('paper_ids')
                    ->title('Select Papers')
                    ->placeholder('Select Papers')
                    ->multiple()
                    ->options($this->papers)
                    ->canSee(count($this->papers) > 0),

                // Select Students
                Relation::make('student_ids')
                    ->fromModel(Student::class, 'id')
                    ->applyScope('forInstitution')
                    ->title('Select students to register for NSIN')
                    ->multiple()
                    ->displayAppend('studentWithNsin'),

            ])
        ];
    }

    /**
     * Update state
     *
     * @param \Orchid\Screen\Repository $repository
     * @param \Illuminate\Http\Request  $request
     *
     * @return \Orchid\Screen\Repository
     */
    public function handle(Repository $repository, Request $request): Repository
    {
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $paperId = $request->input('paper_id');
        $studentIds = $request->input('student_ids.');

        $institution = Institution::with('account')->find($institutionId);
        $institutionCourses = $institution->courses;
        $courses = $institutionCourses->pluck('name', 'id');
        $this->courses = $courses;

        if ($courseId != null) {
            $course = Course::find($courseId);
            $this->papers = $course->papers->pluck('name', 'id');
        }

        return $repository
            ->set('exam_registration_period_id', $request->input('exam_registration_period_id'))
            ->set('institution_id', $institutionId)
            ->set('course_id', $courseId)
            ->set('paper_ids', $paperId);
    }
}
