<?php

namespace App\Orchid\Layouts;

use App\Models\Institution;
use App\Models\RegistrationPeriod;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class RegisterStudentFormListener extends Listener
{
    public $courses = [];

    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'institution_field',
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
                Select::make('institution_field')
                    ->title('Select Institution')
                    ->fromModel(Institution::class, 'name')
                    ->emptyValue('Select an institution'),

                Select::make('course_field')
                    ->title('Program of study')
                    ->options($this->courses)

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
        // Get the selected institution ID from the request
        $institutionId = $request->input('institution_field');

        // Get registration periods
        $registrationPeriods = RegistrationPeriod::where('flag', 1)->get();

        // dd($registrationPeriods);

        // // Fetch the courses assigned to the selected institution using the pivot table
        $courses = Institution::find($institutionId)->courses->pluck('name', 'id');

        // // Convert the courses to an array
        $courseOptions = $courses->toArray();

        // // Update the course select field options with the fetched courses
        $this->courses = $courseOptions;

        return $repository;
    }
}
