<?php

namespace App\Orchid\Layouts;

use App\Models\District;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentRegistration;
use Illuminate\Http\Request;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Listener;
use Orchid\Screen\Repository;
use Orchid\Support\Facades\Layout;

class AddNewStudentForm extends Listener
{
    // Form Values
    public $student = null;

    /**
     * List of field names for which values will be listened.
     *
     * @var string[]
     */
    protected $targets = [
        'new_registration',
        'previous_nsin',
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

                Relation::make('institution_id')
                    ->title('Select Institution')
                    ->fromModel(Institution::class, 'institution_name')
                    ->applyScope('userInstitutions')
                    ->chunk(20),

                Select::make('new_registration')
                    ->title('New / Continuing Student')
                    ->options([
                        'New' => 'New Student',
                        'Continuing' => 'Continuing Student'
                    ])
                    ->empty('Non Selected')
                    ->required(),

                Input::make('previous_nsin')
                    ->title('Previous NSIN')
                    ->required()
                    ->canSee($this->query->get('new_registration') == 'Continuing'),

                Group::make([
                    Input::make('student.surname')
                        ->title('Surname')
                        ->placeholder('Enter Surname')
                        ->value($this->student->surname ?? null)
                        ->required(),


                    Input::make('student.firstname')
                        ->title('First Name')
                        ->required()
                        ->value($this->student->firstname ?? null)
                        ->placeholder('Enter First name'),

                    Input::make('student.othername')
                        ->title('Other Name')
                        ->value($this->student->othername ?? null)
                        ->placeholder('Enter Other name'),
                ]),

                Group::make([
                    Select::make('student.gender')
                        ->options([
                            'Male' => 'MALE',
                            'Female' => 'FEMALE',
                        ])
                        ->title('Student Gender')
                        ->empty('Non Selected')
                        ->value($this->student->gender ?? null)
                        // ->disabled($this->student != null && $this->student->gender != null)
                        ->required(),


                    Input::make('student.dob')
                        ->title('Date Of Birth')
                        ->type('date')
                        ->placeholder('Enter date of birth')
                        ->required()
                        ->value($this->student->dob ?? ''),
                ]),

                Group::make([
                    Input::make('student.telephone')
                        ->title('Phone Number')
                        ->placeholder('Enter phone number')
                        ->value($this->student->telephone ?? null),

                    Input::make('student.email')
                        ->title('Student Email Address')
                        ->value($this->student->email ?? null)
                        ->placeholder('Enter email address'),
                ]),

                Group::make([
                    Select::make('student.district_id')
                        ->title('District')
                        ->fromModel(District::class, 'district_name')
                        ->empty('Non Selected')
                        ->value($this->student->district_id ?? null)
                        ->required(),

                    Input::make('student.location')
                        ->title('Address')
                        ->placeholder('Enter student address')
                        ->value($this->student->location ?? null)
                        ->required(),
                ]),

                Group::make([
                    Input::make('student.passport')
                        ->title('Provide Student Photo')
                        ->type('file')
                        ->name('student.passport')
                        ->placeholder('Enter student passport photo')
                        ->required(),

                    Input::make('student.nin')
                        ->title('National Identification Number / Passport Number')
                        ->required(),

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
        $previousNSIN = $request->get('previous_nsin');

        if ($previousNSIN != null) {
            // Prepopulate our form
            $student = Student::firstWhere('nsin', $previousNSIN);
            $this->student = $student;
        }

        return $repository
            ->set('institution_id', $request->get('institution_id'))
            ->set('new_registration', $request->get('new_registration'))
            ->set('previous_nsin', $request->get('previous_nsin'));
    }
}
