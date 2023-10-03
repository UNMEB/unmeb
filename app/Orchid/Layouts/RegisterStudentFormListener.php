<?php

namespace App\Orchid\Layouts;

use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentRegistrationPeriod;
use App\Models\User;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class RegisterStudentFormListener extends Listener
{

    public $courses = [];
    public $papers = [];
    public $selectedInstitutionId = null;
    public $accountBalance = 0;


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


        // Retrieve the data from your database query
        $registrationPeriods = StudentRegistrationPeriod::where('is_active', '=', '1')
            ->with('year')
            ->select(['id', 'year_id', 'month'])
            ->get();

        // Create the options array for the Select field
        $yearOptions = [];

        foreach ($registrationPeriods as $registrationPeriod) {
            $yearOptions[$registrationPeriod->id] = $registrationPeriod->month . ' / ' . $registrationPeriod->year->name;
        }


        return [
            Layout::rows([
                // Select Registration Period
                Select::make('student_registration_period_id')
                    ->options($yearOptions)
                    ->empty('No select')
                    ->title('Select Registration Period Year'),

                // Select Institution
                Relation::make('institution_id')
                    ->title('Select Institution')
                    ->placeholder('Select an institution')
                ->fromModel(Institution::class, 'name')
                ->applyScope('forInstitution'),

                Select::make('course_id')
                ->title('Select Course')
                ->placeholder('Select a course')
                ->options($this->courses)
                ->canSee(count($this->courses) > 0),

                Relation::make('student_ids.')
                    ->fromModel(Student::class, 'id')
                    ->applyScope('forInstitution')
                    ->title('Select students to register for NSIN')
                    ->multiple()
                    ->displayAppend('fullName'),
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

        $institutionCourses = Institution::find($institutionId)->courses;
        $this->courses = $institutionCourses->pluck('name', 'id');

        return $repository
            ->set('institution_id', $request->input('institution_id'))
        ->set('course_id', $request->input('course_id'))
        ->set('student_registration_period_id', $request->input('student_registration_period_id'));
    }

    public function currentUser(): User
    {
        return auth()->user();
    }
}
