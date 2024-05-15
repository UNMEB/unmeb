<?php

namespace App\Orchid\Layouts;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Quill;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\TextArea;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class StudentResearchUploadForm extends Listener
{

    public $students = [];
    public $courses = [];

    public $course = null;

    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'institution_id',
        'course_id',
    ];

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([
                // Select Institution
                Relation::make('institution_id')
                    ->title('Select Institution')
                    ->fromModel(Institution::class, 'institution_name')
                    ->applyScope('userInstitutions')
                    ->placeholder('Select Institution')
                    ->value(auth()->user()->institution_id)
                    ->required(),

                // Select Program
                Select::make('course_id')
                    ->title('Select Program')
                    ->empty('None Selected', 0)
                    ->placeholder('Select Program')
                    ->options($this->courses)
                    ->canSee(count($this->courses) > 0),

                // Select Student
                Select::make('student_id')
                    ->title('Select Student')
                    ->empty('None Selected', 0)
                    ->placeholder('Select Student')
                    ->options($this->students)
                    ->canSee(count($this->students) > 0),

                TextArea::make('student.research_title')->title('Research Title')->placeholder('Enter the title of the research'),
                Quill::make('student.research_abstract')->title('Research Abstract'),

                Input::make('student.file')
                    ->title('Upload Research Document')
                    ->type('file')

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
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');

        if ($institutionId != null) {
            // Load the courses
            $institution = Institution::find($institutionId);
            $this->courses = $institution->courses->pluck('course_name', 'id');
            $this->institution = $institution->id;
        }

        if ($courseId != null) {
            $this->course = Course::find($courseId);
        }

        if ($this->course != null) {
            $students = Student::from('students as s')
                ->select('s.id', 's.firstname', 's.surname', 's.othername')
                ->join('nsin_student_registrations as nsr', 'nsr.student_id', '=', 's.id')
                ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
                ->join('nsin_registration_periods as nrp', function ($join) {
                    $join->on('nrp.year_id', '=', 'nr.year_id')
                        ->on('nrp.month', '=', 'nr.month');
                })
                ->where('nrp.flag', 1)
                ->where('nr.institution_id', $institutionId)
                ->where('nr.course_id', $courseId)
                ->get();

            $this->students = $students->map(function ($data) {
                return [
                    'id' => $data->id,
                    'name' => $data->surname . ' ' . $data->othername . ' ' . $data->firstname
                ];
            })->pluck('name', 'id');
        }

        return $repository
            ->set('institution_id', $institutionId)
            ->set('course_id', $courseId);
    }
}
