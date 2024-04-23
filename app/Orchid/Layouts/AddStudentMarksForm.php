<?php

namespace App\Orchid\Layouts;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Paper;
use App\Models\RegistrationPeriod;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class AddStudentMarksForm extends Listener
{

    public $courses = [];
    public $papers = [];
    public $students = [];

    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'institution_id',
        'course_id',
        'paper_id',
        'paper_type',
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

                Group::make([
                    // Select Exam Registration Period
                    Select::make('exam_registration_period_id')
                        ->options($yearOptions)
                        ->empty('None Selected')
                        ->title('Select Exam Registration Period')
                        ->required(),

                    // Select Institution
                    Relation::make('institution_id')
                        ->title('Select Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->placeholder('Select Institution')
                        ->canSee(!auth()->user()->inRole('institution'))
                        ->required(),
                ]),

                Group::make([
                    // Select Year of Study
                    Select::make('year_of_study')
                        ->empty('None Selected')
                        ->title('Select Year of Study')
                        ->options([
                            'Year 1 Semester 1' => 'Year 1 Semester 1',
                            'Year 1 Semester 2' => 'Year 1 Semester 2',
                            'Year 2 Semester 1' => 'Year 2 Semester 1',
                            'Year 3 Semester 1' => 'Year 3 Semester 1',
                            'Year 3 Semester 2' => 'Year 3 Semester 2',
                        ])
                        ->canSee($this->query->has('institution_id')),

                    // Select Program
                    Select::make('course_id')
                        ->title('Select Program')
                        ->empty('None Selected', 0)
                        ->placeholder('Select Program')
                        ->options($this->courses)
                        ->canSee(count($this->courses) > 0 && $this->query->has('year_of_study')),


                ]),

                Group::make([
                    // Select Paper
                    Select::make('paper_id')
                        ->title('Select Paper')
                        ->empty('None Selected', 0)
                        ->placeholder('Select Paper')
                        ->options($this->papers)
                        ->canSee(count($this->papers) > 0),

                    // Select Paper Type (Practical or Theory)
                    Select::make('paper_type')
                        ->empty('None Selected')
                        ->title('Select Paper Type')
                        ->options([
                            'Practical' => 'Practical',
                            'Theory' => 'Theory',
                        ])
                        ->canSee($this->query->has('paper_id')),
                ]),

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
        $studentId = $request->get('student_id');
        $paperId = $request->get('paper_id');
        $yearOfStudy = $request->get('year_of_study');
        $paperType = $request->get('paper_type');

        if ($institutionId != null) {
            // Load the courses
            $institution = Institution::find($institutionId);
            $this->courses = $institution->courses->pluck('course_name', 'id');
        }

        if ($courseId != null) {
            $course = Course::find($courseId);
        }

        if ($yearOfStudy) {
            $papers = Paper::select('papers.*', 'course_paper.course_id as pivot_course_id', 'course_paper.paper_id as pivot_paper_id', 'course_paper.flag as pivot_flag')
                ->join('course_paper', 'papers.id', '=', 'course_paper.paper_id')
                ->where('course_paper.course_id', $courseId)
                ->where('papers.year_of_study', $yearOfStudy)
                ->orderBy('papers.paper_name')
                ->get();

            $allPapers = [];

            foreach ($papers as $paper) {
                $modifiedPaperName = $paper->paper_name . ' ( ' . $paper->paper . ' - ' . $paper->code . ' )';

                $allPapers[] = (object) [
                    'id' => $paper->id,
                    'paper_name' => $modifiedPaperName
                ];
            }

            $this->papers = collect($allPapers)->pluck('paper_name', 'id');
        }

        return $repository
            ->set('exam_registration_period_id', $examRegistrationPeriodId)
            ->set('institution_id', $institutionId)
            ->set('course_id', $courseId)
            ->set('paper_id', $paperId)
            ->set('student_id', $studentId)
            ->set('year_of_study', $yearOfStudy)
            ->set('paper_type', $paperType);
    }
}
