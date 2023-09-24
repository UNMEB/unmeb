<?php

namespace App\Orchid\Screens\Staff;

use App\Imports\StaffImport;
use App\Models\District;
use App\Models\Institution;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Components\Cells\DateTimeSplit;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Upload;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Color;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class StaffListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'staff' => Staff::with('institution')->latest()->get()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Staff';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add Staff')
                ->modal('createStaffModal')
                ->method('create')
                ->icon('plus'),
            ModalToggle::make('Import Staff')
                ->modal('uploadStaffModal')
                ->method('upload')
                ->icon('upload'),
            Button::make('Export Data')
                ->method('download')
                ->rawClick(false)
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
            Layout::table('staff', [
                TD::make('id', 'ID')
                    ->width('100'),
                TD::make('name', _('Name')),
                TD::make('designation', _('Designation')),
                TD::make('status', _('Status')),
                TD::make('education', _('Education')),
                TD::make('qualification', _('Qualification')),
                TD::make('council', _('Council')),
                TD::make('reg_no', _('Registration No.')),
                TD::make('experience', _('Experience')),
                TD::make('telephone', _('Telephone')),
                TD::make('email', _('Email')),
                TD::make('bank_details', _('Bank Detaills'))->render(function (Staff $staff) {
                    return ModalToggle::make('View Bank Details')->type(Color::LINK);
                }),
            ]),
            Layout::modal('createStaffModal', Layout::rows([

                Select::make('staff.institution_id')
                    ->title('Select Institution')
                    ->fromModel(Institution::class, 'name')
                    ->empty('Select Institution'),

                Group::make([
                    Input::make('staff.name')
                        ->title('Staff Name')
                        ->placeholder('Enter staff name'),

                    Input::make('staff.telephone')
                        ->title('Phone Number')
                        ->type('tel')
                        ->placeholder('Enter Phone Number'),
                ]),

                Group::make([
                    Input::make('staff.email')
                        ->title('Email Address')
                        ->type('email')
                        ->placeholder('Enter email address'),
                    Select::make('staff.status')
                        ->title('Status')
                        ->options([
                            'Part Time' => 'Part Time',
                            'Full Time' => 'Full Time'
                        ])
                        ->empty('Select Employment Status'),
                ]),

                Group::make([
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

                    Input::make('staff.council')
                        ->title('Council')
                        ->placeholder('Enter Council'),

                ]),

                Group::make([
                    Input::make('staff.reg_no')
                        ->title('Registration Number')
                        ->placeholder('Registration Number'),

                    Input::make('staff.reg_date')
                        ->title('Registration Date')
                        ->type('date')
                        ->placeholder('Registration Date'),
                ]),

                Group::make([
                    Input::make('staff.lic_exp')
                        ->title('License Expiry')
                        ->type('date')
                        ->placeholder('License Expiry'),
                ]),

                Group::make([
                    Input::make('staff.designation')
                        ->title('Designation')
                        ->placeholder('Designation'),

                    Select::make('staff.district_id')
                        ->title('Duty Station')
                        ->fromModel(District::class, 'name')
                        ->empty('Select Duty Station'),
                ]),

                Group::make([
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
                ]),

                Group::make([
                    Input::make('staff.bank')
                        ->title('Bank Name')
                        ->placeholder('Enter bank name'),
                    Input::make('staff.branch')
                        ->title('Branch Name')
                        ->placeholder('Enter staff name'),

                ]),

                Group::make([
                    Input::make('staff.acc_name')
                        ->title('Account Name')
                        ->placeholder('Enter Account Name'),

                    Input::make('staff.acc_no')
                        ->title('Account Number')
                        ->placeholder('Enter Account Number'),
                ]),
            ]))
                ->size(Modal::SIZE_LG)
                ->title('Create Staff')
                ->applyButton('Create Staff'),

            Layout::modal('editStaffModal', Layout::rows([]))
                ->title('Upload Staff')
                ->applyButton('Upload Staff'),

        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function create(Request $request)
    {
        $request->validate([
            'staff.institution_id' => 'required',
            'staff.district_id' => 'required',
            'staff.name' => 'required',
            'staff.designation' => 'required',
            'staff.status' => 'required',
            'staff.education' => 'required',
            'staff.qualification' => 'required',
            'staff.council' => 'required',
            'staff.reg_no' => 'required',
            'staff.reg_date' => 'required|date',
            'staff.lic_exp' => 'required|date',
            'staff.experience' => 'required',
            'staff.telephone' => 'required',
            'staff.email' => 'required',
            'staff.bank' => 'required',
            'staff.branch' => 'required',
            'staff.acc_no' => 'required',
            'staff.acc_name' => 'required',
        ]);

        $staff = new Staff();
        $staff->institution_id = $request->input('staff.institution_id');
        $staff->district_id = $request->input('staff.district_id');
        $staff->name = $request->input('staff.name');
        $staff->designation = $request->input('staff.designation');
        $staff->status = $request->input('staff.status');
        $staff->education = $request->input('staff.education');
        $staff->qualification = $request->input('staff.qualification');
        $staff->council = $request->input('staff.council');
        $staff->reg_no = $request->input('staff.reg_no');
        $staff->reg_date = $request->input('staff.reg_date');
        $staff->lic_exp = $request->input('staff.lic_exp');
        $staff->experience = $request->input('staff.experience');
        $staff->telephone = $request->input('staff.telephone');
        $staff->email = $request->input('staff.email');
        $staff->bank = $request->input('staff.bank');
        $staff->branch = $request->input('staff.branch');
        $staff->acc_no = $request->input('staff.acc_no');
        $staff->acc_name = $request->input('staff.acc_name');
        $staff->receipt = $request->input('staff.receipt');
        $staff->institution_id = $request->input('staff.institution_id');
        $staff->save();

        Alert::success("Year was created");
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function upload(Request $request)
    {
        // Define custom error messages for validation
        $customMessages = [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'The file must be a CSV file.',
            'file.max' => 'The file size must not exceed 64MB.',
        ];

        // Validate the request data using the defined rules and custom messages
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|max:64000', // 64MB in kilobytes
            // Add any other validation rules you need for other fields
        ], $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Retrieve the uploaded file from the request
        $uploadedFile = $request->file('file');

        // Use Laravel Excel to import the data using your custom importer
        try {
            // Get the path of the uploaded file
            $filePath = $uploadedFile->path();

            // Import the data using your custom importer
            Excel::import(new StaffImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("Year data imported successfully");

            // Data import was successful
            return redirect()->back()->with('success', 'Years data imported successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during import
            Alert::error($e->getMessage());

            return redirect()->back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
    }
}
