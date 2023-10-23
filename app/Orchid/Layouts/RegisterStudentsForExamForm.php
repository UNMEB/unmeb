<?php

namespace App\Orchid\Layouts;

use App\Models\Course;
use App\Models\Institution;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class RegisterStudentsForExamForm extends Listener
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
        'student_ids',
        'trial'
    ];

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        $registrationPeriods = RegistrationPeriod::where('flag', '=', '1')->get();

        $yearOptions = [];

        foreach ($registrationPeriods as $registrationPeriod) {
            $yearOptions[$registrationPeriod->id] = $registrationPeriod->reg_start_date . ' - ' . $registrationPeriod->reg_end_date;
        }

        return [
            Layout::rows([
                // Select Exam Registration Period
                Select::make('exam_registration_period_id')
                    ->options($yearOptions)
                    ->empty('None Selected')
                    ->title('Select Exam Registration Period'),

                // Select Year of Study
                Select::make('year_of_study')
                    ->empty('None Selected')
                    ->title('Select Year of Study')
                    ->options([
                        'Year 1 Semester 1' => 'Year 1 Semester 1',
                        'Year 1 Semester 2' => 'Year 1 Semester 2',
                        'Year 2 Semester 1' => 'Year 2 Semester 1',
                        'Year 3 Semester 2' => 'Year 2 Semester 2',
                        'Year 3 Semester 1' => 'Year 3 Semester 1',
                        'Year 4 Semester 2' => 'Year 3 Semester 2',
                    ]),

                // Select Institution
                Relation::make('institution_id')
                    ->title('Select Institution')
                    ->fromModel(Institution::class, 'institution_name')
                    ->applyScope('userInstitutions')
                    ->placeholder('Select Institution'),

                // Select Program
                Select::make('course_id')
                    ->title('Select Program')
                    ->empty('None Selected', 0)
                ->placeholder('Select Program')
                    ->options($this->courses)
                    ->canSee(count($this->courses) > 0),

                // Select Paper
                Select::make('paper_ids')
                    ->title('Select Papers')
                    ->placeholder('Select Papers')
                    ->multiple()
                    ->options($this->papers)
                    ->canSee(count($this->papers) > 0),

                // Select Trial
                Select::make('trial')
                    ->title('Trial Number')
                    ->required()
                    ->options([
                        'First' => 'First Attempt',
                        'Second' => 'Second Attempt',
                        'Third Attempt' => 'Third Attempt'
                    ]),

                // Select Students
                Relation::make('student_ids')
                    ->fromModel(Student::class, 'id')
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
        $examRegistrationPeriodId = $request->get('exam_registration_period_id');
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');
        $studentIds = $request->get('student_ids');
        $paperIds = $request->get('paper_ids');
        $yearOfStudy = $request->get('year_of_study');
        $trial = $request->get('trial');

        if ($institutionId != null) {
            // Load the courses
            $institution = Institution::find($institutionId);
            $this->courses = $institution->courses->pluck('course_name', 'id');
        }

        if ($courseId != null) {
            $course = Course::find($courseId);
            $this->papers = $course->papers->pluck('paper_name', 'id');
        }

        return $repository
            ->set('exam_registration_period_id', $examRegistrationPeriodId)
            ->set('institution_id', $institutionId)
            ->set('course_id', $courseId)
            ->set('paper_ids', $paperIds)
            ->set('student_ids', $studentIds)
            ->set('year_of_study',  $yearOfStudy)
            ->set('trial', $trial);
    }
}
