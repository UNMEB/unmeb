<?php

namespace App\Orchid\Layouts;

use App\Models\Country;
use App\Models\Course;
use App\Models\District;
use App\Models\Institution;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class AddStudentWithNSINForm extends Listener
{

    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [];

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    protected function layouts(): iterable
    {
        return [
            Layout::rows([

                Input::make('student.nsin')
                    ->title('Student NSIN')
                    ->required(),

                Group::make([
                    Input::make('student.nin')
                        ->title('National Identification Number'),

                    Input::make('student.passport_number')
                        ->title('Passport Number'),

                    Input::make('student.lin')
                        ->title('Learners Identification Number'),
                ]),

                Group::make([
                    Input::make('student.surname')
                        ->title('Surname')
                        ->placeholder('Enter Surname')
                        ->required(),
                    Input::make('student.firstname')
                        ->title('First Name')
                        ->required()
                        ->placeholder('Enter First name'),

                    Input::make('student.othername')
                        ->title('Other Name')
                        ->placeholder('Enter Other name'),
                ]),

                Relation::make('student.institution_id')
                    ->title('Select Institution')
                    ->placeholder('Select User Institution')
                    ->fromModel(Institution::class, 'institution_name', 'id')
                    ->applyScope('userInstitutions')
                    ->value(auth()->user()->institution_id ?? null)
                    ->canSee(!auth()->user()->inRole('institution'))
                    ->required(),

                Relation::make('student.applied_program')
                    ->title('Select Program')
                    ->placeholder('Select Program')
                    ->fromModel(Course::class, 'course_name')
                    ->required(),

                Group::make([

                    Select::make('student.gender')
                        ->options([
                            'Male' => 'MALE',
                            'Female' => 'FEMALE',
                        ])
                        ->title('Student Gender')
                        ->empty('Non Selected')
                        ->required(),

                        Input::make('student.dob')
                        ->title('Date Of Birth')
                        ->type('date')
                        ->placeholder('Enter date of birth')
                        ->required()
                        ->noCalendar(),
                ]),

                Group::make([
                    Input::make('student.telephone')
                        ->title('Phone Number')
                        ->placeholder('Enter phone number'),

                    Input::make('student.email')
                        ->title('Student Email Address')
                        ->placeholder('Enter email address'),
                ]),

                Group::make([

                    Select::make('student.country_id')
                        ->title('Country')
                        ->fromModel(Country::class, 'name')
                        ->empty('Non Selected'),

                    Select::make('student.district_id')
                        ->title('District')
                        ->fromModel(District::class, 'district_name')
                        ->empty('Non Selected'),

                    Input::make('student.location')
                        ->title('Home Address')
                        ->placeholder('Enter student address')
                        ->required(),
                ]),

                Picture::make('student.passport')
                    ->title('Provide Student Photo')
                    ->name('student.passport')
                    ->placeholder('Enter student passport photo')
                    ->required()
                    ->width(480)
                    ->height(480),
                
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
        return $repository;
    }
}
