<?php

namespace App\Orchid\Layouts;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Paper;
use App\Models\RegistrationPeriod;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class ApplyForExamsForm extends Listener
{

    public $courses = [];
    public $papers = [];

    public $selectedPapers = [];

    public $institution = null;
    public $course;

    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'institution_id',
        'course_id',
        'paper_ids',
        'year_of_study',
        'trial',
        'selected_papers'
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

        $selectedPapers = $this->selectedPapers;

        return [
            Layout::rows([
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
                    ->value(auth()->user()->institution_id)
                    ->required(),

                // Select Year of Study
                Select::make('year_of_study')
                    ->empty('None Selected')
                    ->title('Select Year of Study')
                    ->options([
                        'Year 1 Semester 1' => 'Year 1 Semester 1',
                        'Year 1 Semester 2' => 'Year 1 Semester 2',
                        'Year 2 Semester 1' => 'Year 2 Semester 1',
                        'Year 2 Semester 2' => 'Year 2 Semester 2',
                        'Year 3 Semester 1' => 'Year 3 Semester 1',
                        'Year 3 Semester 2' => 'Year 3 Semester 2',
                    ])
                    ->required(),


                // Select Program
                Select::make('course_id')
                    ->title('Select Program')
                    ->empty('None Selected', 0)
                    ->placeholder('Select Program')
                    ->options($this->courses)
                    ->canSee(count($this->courses) > 0),

                // Select Trial
                Select::make('trial')
                    ->title('Trial Number')
                    ->required()
                    ->options([
                        'First' => 'First Attempt',
                        'Second' => 'Second Attempt',
                        'Third Attempt' => 'Third Attempt'
                    ])
                    ->empty('None Selected')
                    ->required(),

                // If we select Second or third, clear the paper ids
                // if($this->query->has('trial') )

                // Select Paper
                Select::make('paper_ids.') // Note the dot at the end to indicate an array
                    ->title('Select Papers')
                    ->placeholder('Select Papers')
                    ->multiple()
                    ->options($this->papers)
                    ->canSee(count($this->papers) > 0)
                    ->values($this->selectedPapers),
            ]),


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
        $paperIds = $request->get('paper_ids');
        $yearOfStudy = $request->get('year_of_study');
        $trial = $request->get('trial');

        if ($institutionId != null) {
            // Load the courses
            $institution = Institution::find($institutionId);
            $this->courses = $institution->courses->pluck('course_name', 'id');
            $this->institution = $institution->id;
        }

        if ($courseId != null) {
            $this->course = Course::find($courseId);
        }

        if ($yearOfStudy != null && $courseId != null && $this->course != null) {

            $papers = Paper::select('papers.*', 'course_paper.course_id as pivot_course_id', 'course_paper.paper_id as pivot_paper_id', 'course_paper.flag as pivot_flag')
                ->join('course_paper', 'papers.id', '=', 'course_paper.paper_id')
                ->where('course_paper.course_id', $courseId)
                ->where('papers.year_of_study', $yearOfStudy)
                ->where('papers.code', 'LIKE', $this->course->course_code . '%')
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

            if ($trial == 'First') {
                $this->selectedPapers = $this->papers;
                $repository->set('paper_ids', $this->selectedPapers);
            } else {
                $this->selectedPapers = [];
                $repository->set('paper_ids', $this->selectedPapers);
            }

            // dd($repository);
        }

        return $repository
            ->set('exam_registration_period_id', $examRegistrationPeriodId)
            ->set('institution_id', $institutionId)
            ->set('course_id', $courseId)
            ->set('year_of_study', $yearOfStudy)
            ->set('trial', $trial);
    }
}