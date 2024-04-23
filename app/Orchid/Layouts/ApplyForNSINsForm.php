<?php

namespace App\Orchid\Layouts;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistrationPeriod;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class ApplyForNSINsForm extends Listener
{

    public $courses  = [];
    public $institutionId;

    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'institution_id',
        'nsin_registration_period_id',
        'course_id'
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
                Relation::make('institution_id')
                    ->title('Select Institution')
                    ->placeholder('Select User Institution')
                    ->fromModel(Institution::class, 'institution_name', 'id')
                    ->applyScope('userInstitutions')
                    ->value(auth()->user()->institution_id)
                    // ->disabled(!auth()->user()->hasAccess('platform.internals.all_institutions'))
                    ->canSee(!auth()->user()->inRole('institution'))
                    ->required(),

                // Select Nsin Registration Period
                Select::make('nsin_registration_period_id')
                    ->options($yearOptions)
                    ->empty('None Selected')
                    ->title('Select Nsin Registration Period'),

                Select::make('course_id')
                    ->title('Select Program')
                    ->empty('No Program Selected')
                    ->options($this->courses)
                    ->required(),

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
        $nsinRegistrationPeriodId = $request->get('nsin_registration_period_id');

        if ($institutionId != null) {
            // Load the courses
            $institution = Institution::find($institutionId);
            $this->courses = $institution->courses->pluck('course_name', 'id');
        }

        $courseId = $request->get('course_id');

        if ($courseId != null) {
            $course = Course::find($courseId);
        }

        return $repository
            ->set('nsin_registration_period_id', $nsinRegistrationPeriodId)
            ->set('institution_id', $institutionId)
            ->set('course_id', $courseId);
    }
}
