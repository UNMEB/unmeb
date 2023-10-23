<?php

namespace App\Orchid\Layouts;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistrationPeriod;
use App\Models\Student;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class RegisterStudentsForNinForm extends Listener
{

    public $courses  = [];
    public $students = [];


    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'institution_id',
        'course_id',
        'student_ids'
    ];

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        $registrationPeriods = NsinRegistrationPeriod::select('nsin_registration_periods.id', 'years.year', 'month')
            ->join('years', 'nsin_registration_periods.year_id', '=', 'years.id')
            ->where('nsin_registration_periods.flag', 1)
            ->get();

        $yearOptions = [];

        foreach ($registrationPeriods as $registrationPeriod) {
            $yearOptions[$registrationPeriod->id] = $registrationPeriod->month . ' - ' . $registrationPeriod->year;
        }

        return [
            Layout::rows([

                // Select Nsin Registration Period
                Select::make('nsin_registration_period_id')
                    ->options($yearOptions)
                    ->empty('None Selected')
                    ->title('Select Nsin Registration Period'),


                Relation::make('institution_id')
                    ->title('Select Institution')
                    ->fromModel(Institution::class, 'institution_name')
                    ->applyScope('userInstitutions')
                    ->chunk(20),

                Select::make('course_id')
                    ->title('Select Program')
                    ->options($this->courses)
                    ->canSee(count($this->courses) > 0),

                // Select Students
                Relation::make('student_ids')
                    ->fromModel(Student::class, 'id')
                    ->title('Select students to register for NSIN')
                    ->multiple()
                    ->displayAppend('fullName')
                    ->searchColumns('surname', 'othername', 'firstname', 'nsin'),
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
        $nsinRegistrationPeriodId = $request->get('nsin_registration_period_id');
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');

        // Get the institution
        $institution = Institution::find($institutionId);

        $this->courses = $institution->courses->pluck('course_name', 'id');

        return $repository
            ->set('nsin_registration_period_id', $nsinRegistrationPeriodId)
            ->set('institution_id', $institutionId)
            ->set('course_id', $courseId)
            ->set('studentIds');
    }
}
