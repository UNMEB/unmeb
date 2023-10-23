<?php

namespace App\Orchid\Screens\Administration\Student;

use App\Models\District;
use App\Models\Institution;
use App\Models\Student;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Matrix;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class StudentEditScreen extends Screen
{

    /**
     * @var Student
     */
    public $student;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Student $student): iterable
    {
        $student->load(['district']);

        return [
            'student' => $student,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->student->exists ? 'Edit Student' : 'Create Student';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all registered students, including institutions they belong to.';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            Button::make(__('Save'))
                ->icon('bs.check-circle')
                ->method('save'),
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            Layout::block(
                Layout::rows([
                    Input::make('student.firstname')
                        ->title('First Name')
                        ->placeholder('Enter First Name'),

                    Input::make('student.surname')
                        ->title('Surname')
                        ->placeholder('Enter Surname'),

                    Input::make('student.othername')
                        ->title('Other Name')
                        ->placeholder('Enter Other Name'),

                    // Gender
                    Select::make('student.gender')
                        ->title('Gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female',
                        ]),

                    Input::make('student.dob')
                        ->title('Date of Birth')
                        ->placeholder('Enter Date of Birth'),

                    // Select District
                    Select::make('student.district_id')
                        ->fromModel(District::class, 'district_name')
                        ->title('District')
                        ->placeholder('Select District'),

                    // Select Country
                    Select::make('student.country')
                        ->options([
                            'Uganda' => 'Uganda',
                            'Kenya' => 'Kenya',
                            'Tanzania' => 'Tanzania',
                            'Rwanda' => 'Rwanda',
                            'Burundi' => 'Burundi',
                            'South Sudan' => 'South Sudan',
                            'DRC' => 'DRC',
                            'Nigeria' => 'Nigeria',
                            'Ethiopia' => 'Ethiopia',
                        ])
                        ->title('Country')
                        ->placeholder('Select Country')
                        ->help('Select country or type to Add Country')
                        ->allowAdd(),

                    Input::make('student.location')
                        ->title('Location')
                        ->placeholder('Enter Location'),

                    // NSIN
                    Input::make('student.NSIN')
                        ->title('NSIN')
                        ->placeholder('Enter NSIN'),

                    // Telephone
                    Input::make('student.telephone')
                        ->title('Telephone')
                        ->placeholder('Enter Telephone'),

                    // Email Address
                    Input::make('student.email')
                        ->title('Email')
                        ->placeholder('Enter Email'),

                ])
            )
                ->title('Personal Information')
                ->description('Please enter the required information')
                ->commands(
                    Button::make(__('Save'))
                        ->icon('bs.check-circle')
                        ->method('save')
                        ->canSee($this->student->exists)
                        ->type(Color::BASIC)
                ),

        ];
    }
}
