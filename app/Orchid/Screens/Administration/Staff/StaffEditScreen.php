<?php

namespace App\Orchid\Screens\Administration\Staff;

use App\Models\Institution;
use App\Models\Staff;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Layout;

class StaffEditScreen extends Screen
{

    /**
     * @var Staff
     */
    public $staff;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Staff $staff): iterable
    {
        $staff->load(['institution']);

        return [
            'staff' => $staff,
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->staff->exists ? 'Edit Staff' : 'Create Staff';
    }

    /**
     * Display header description.
     */
    public function description(): ?string
    {
        return 'A comprehensive list of all registered staff, including institutions they belong to.';
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
                    Input::make('staff.staff_name')
                        ->title('Staff Name')
                        ->placeholder('Enter staff name'),

                    Input::make('staff.telephone')
                        ->title('Phone Number')
                        ->type('tel')
                        ->placeholder('Enter Phone Number'),

                    Input::make('staff.email')
                        ->title('Email Address')
                        ->type('email')
                        ->placeholder('Enter email address'),
                ])
            )
                ->title('Personal Information')
                ->description('Please enter the required information')
                ->commands(
                    Button::make(__('Save'))
                        ->icon('bs.check-circle')
                        ->method('save')
                        ->canSee($this->staff->exists)
                        ->type(Color::BASIC)
                ),

            Layout::block(
                Layout::rows([
                    Select::make('staff.education')
                        ->title('Education Level')
                        ->options([
                            'PHD' => 'PHD',
                            'Masters' => 'Masters',
                            'Bachelors' => 'Bachelors',
                            'Diploma' => 'Diploma',
                            'Certificate' => 'Certificate'
                        ])
                        ->empty('Select Level of Education'),

                    Input::make('staff.reg_date')
                        ->title('Registration Date')
                        ->type('date')
                        ->placeholder('Registration Date'),

                    Input::make('staff.reg_no')
                        ->title('Registration Number')
                        ->placeholder('Registration Number'),

                    Input::make('staff.lic_exp')
                        ->title('License Expiry')
                        ->type('date')
                        ->placeholder('License Expiry'),
                ])
            )
                ->title('Education Information')
                ->description('Please enter the required information')
                ->commands(
                    Button::make(__('Save'))
                        ->icon('bs.check-circle')
                        ->method('save')
                        ->canSee($this->staff->exists)
                        ->type(Color::BASIC)
                ),

            Layout::block(
                Layout::rows([Relation::make('staff.institution_id')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->title('Select Institution')
                        ->placeholder('Select an institution'),


                    Select::make('staff.status')
                        ->title('Status')
                        ->options([
                            'Part' => 'Part Time',
                            'Full' => 'Full Time'
                        ])
                        ->empty('Select Employment Status'),

                    Input::make('staff.designation')
                        ->title('Designation')
                        ->placeholder('Designation'),

                    Input::make('staff.experience')
                        ->title('Level of Experience')
                        ->type('number')
                        ->placeholder('Level of Experience'),

                    Select::make('staff.qualification')
                        ->title('Qualification')
                        ->options([
                            'Nurse - Diploma' => 'Nurse - Diploma',
                            'Nurse - Graduate' => 'Nurse - Graduate',
                            'Midwifery - Graduate' => 'Midwifery - Graduate',
                            'Midwifery - Diploma' => 'Midwifery - Diploma',
                            'Advanced Palliative Care Nurse' => 'Advanced Palliative Care Nurse',
                            'Advanced Diploma Public Health Nurse' => 'Advanced Diploma Public Health Nurse',
                            'Diploma Paediatric and Child Health Nurse' => 'Diploma Paediatric and Child Health Nurse',
                            'Diploma Mental Health Nurse' => 'Diploma Mental Health Nurse',
                            'Health Tutor - Degree' => 'Health Tutor - Degree',
                            'Health Tutor - Post-Graduate' => 'Health Tutor - Post-Graduate',
                            'Health Tutor - Diploma in Medical Education' => 'Health Tutor - Diploma in Medical Education'
                        ])
                        ->empty('Select Qualification'),



                ])
            )
                ->title('Professional Information')
                ->description('Please provide complete and correct professional information.')
                ->commands(
                    Button::make(__('Save'))
                        ->icon('bs.check-circle')
                        ->method('save')
                        ->canSee($this->staff->exists)
                        ->type(Color::BASIC)
                ),

            Layout::block(
                Layout::rows([
                    Input::make('staff.bank')->title('Bank Name')
                        ->placeholder('Enter bank name'),
                    Input::make('staff.branch')->title('Bank Branch')
                        ->placeholder('Enter bank branch'),
                    Input::make('staff.acc_name')->title('Account Name')
                        ->placeholder('Enter account name'),
                    Input::make('staff.acc_no')->title('Account Number')
                        ->placeholder('Enter account number'),
                ])
            )
                ->title('Banking Information')
                ->description('Please provide complete and correct banking information.')
                ->commands(
                    Button::make(__('Save'))
                        ->icon('bs.check-circle')
                        ->method('save')
                        ->canSee($this->staff->exists)
                        ->type(Color::BASIC)
                )


        ];
    }
}
