<?php

namespace App\Orchid\Layouts;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class ExportNSINRegistrationForm extends Listener
{
    public $courses = [];
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
        $nsinRegistrationPeriods = NsinRegistrationPeriod::where('flag', 1)->get();

        $yearOptions = [];

        foreach ($nsinRegistrationPeriods as $nsinRegistrationPeriod) {
            $yearOptions[$nsinRegistrationPeriod->id] = $nsinRegistrationPeriod->month . ' ' . $nsinRegistrationPeriod->year->year;
        }

        return [
            Layout::rows([
                // Select NSIN Registration Period
                Select::make('nsin_registration_period_id')
                    ->options($yearOptions)
                    ->empty('None Selected')
                    ->title('Select NSIN Registration Period')
                    ->required(),

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

                // Select NSIN Status
                Select::make('nsin_status')
                    ->title('Select NSIN status')
                    ->options([
                        0 => 'PENDING',
                        1 => 'APPROVED',
                        2 => 'REJECTED'
                    ])
                    ->empty('Select NSIN status')
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

        if ($institutionId != null) {
            // Load the courses
            $institution = Institution::find($institutionId);
            $this->courses = $institution->courses->pluck('course_name', 'id');
            $this->institution = $institution->id;
        }

        if ($courseId != null) {
            $this->course = Course::find($courseId);
        }

        return $repository
            ->set('nsin_registration_period_id', $nsinRegistrationPeriodId)
            ->set('institution_id', $institutionId)
            ->set('course_id', $courseId);
    }
}
