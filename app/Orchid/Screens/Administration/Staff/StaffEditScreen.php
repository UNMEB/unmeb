<?php

namespace App\Orchid\Screens\Administration\Staff;

use App\Models\Institution;
use App\Models\Staff;
use Exception;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Picture;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Screen;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Arr;

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
                    Group::make([
                        Picture::make('staff.picture')
                            ->title('Staff Member Photo')
                            ->width('270')
                            ->height('270')
                    ]),

                    Group::make([

                        Input::make('staff.staff_name')
                            ->title('Staff Name')
                            ->placeholder('Enter staff name'),

                        Input::make('staff.telephone')
                            ->title('Phone Number')
                            ->type('tel')
                            ->min('10')
                            ->max('12')
                            ->placeholder('Enter Phone Number'),

                        Input::make('staff.email')
                            ->title('Email Address')
                            ->type('email')
                            ->placeholder('Enter email address'),
                    ]),
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

                    Group::make([
                        Input::make('staff.reg_date')
                            ->title('Registration Date')
                            ->type('date')
                            ->placeholder('Registration Date'),

                        Input::make('staff.reg_no')
                            ->title('Registration Number')
                            ->type('numeric')
                            ->placeholder('Registration Number'),
                    ]),

                    Group::make([
                        Input::make('staff.council')
                            ->title('Council')
                            ->placeholder('Registration Council'),

                        Input::make('staff.lic_exp')
                            ->title('License Expiry')
                            ->type('date')
                            ->placeholder('License Expiry'),
                    ]),

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
                Layout::rows([

                    Group::make([
                        Relation::make('staff.institution_id')
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
                    ]),

                    Group::make([
                        Input::make('staff.designation')
                            ->title('Designation')
                            ->placeholder('Designation'),

                        Input::make('staff.experience')
                            ->title('Level of Experience')
                            ->type('number')
                            ->min(0)
                            ->placeholder('Level of Experience'),
                    ]),

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

                    Group::make([
                        Input::make('staff.bank')->title('Bank Name')
                            ->placeholder('Enter bank name'),
                        Input::make('staff.branch')->title('Bank Branch')
                            ->placeholder('Enter bank branch'),
                    ]),

                    Group::make([
                        Input::make('staff.acc_name')->title('Account Name')
                            ->placeholder('Enter account name'),
                        Input::make('staff.acc_no')->title('Account Number')
                            ->placeholder('Enter account number'),
                    ]),
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

    public function save(Request $request)
    {
        $this->validate($request, [
            'staff.staff_name' => 'required',
            'staff.telephone' => 'required',
            'staff.email' => 'required',
            'staff.education' => 'required',
            'staff.reg_date' => 'required',
            'staff.reg_no' => 'required',
            'staff.lic_exp' => 'required',
            'staff.institution_id' => 'required',
            'staff.status' => 'required',
            'staff.designation' => 'required',
            'staff.experience' => 'required',
            'staff.qualification' => 'required',
            'staff.bank' => 'required',
            'staff.branch' => 'required',
            'staff.acc_name' => 'required',
            'staff.acc_no' => 'required',
            'staff.picture' => 'required',
            'staff.council' => 'required'
        ]);

        $staffId = $this->staff->id;

        // Check if we are updating an existing staff or creating a new one
        if ($staffId != null) {
            // Updating existing staff
            $staff = Staff::findOrFail($staffId);
        } else {
            // Creating new staff
            $staff = new Staff();
        }

        // Assign values from the request to the staff model
        $staff->staff_name = $request->input('staff.staff_name');
        $staff->telephone = $request->input('staff.telephone');
        $staff->email = $request->input('staff.email');
        $staff->education = $request->input('staff.education');
        $staff->reg_date = $request->input('staff.reg_date');
        $staff->reg_no = $request->input('staff.reg_no');
        $staff->lic_exp = $request->input('staff.lic_exp');
        $staff->institution_id = $request->input('staff.institution_id');
        $staff->status = $request->input('staff.status');
        $staff->designation = $request->input('staff.designation');
        $staff->experience = $request->input('staff.experience');
        $staff->qualification = $request->input('staff.qualification');
        $staff->bank = $request->input('staff.bank');
        $staff->branch = $request->input('staff.branch');
        $staff->acc_name = $request->input('staff.acc_name');
        $staff->acc_no = $request->input('staff.acc_no');
        $staff->picture = $request->input('staff.picture');
        $staff->council = $request->input('staff.council');

        // Save the staff model
        $staff->save();

        Alert::success('Staff record saved');

        return redirect()->route('platform.staff');
    }
}
