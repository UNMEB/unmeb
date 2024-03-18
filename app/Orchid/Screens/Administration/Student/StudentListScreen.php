<?php

namespace App\Orchid\Screens\Administration\Student;

use App\Exports\StudentExport;
use App\Models\Account;
use App\Models\Course;
use App\Models\District;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Year;
use App\Orchid\Layouts\AddNewStudentForm;
use App\Orchid\Layouts\RegisterStudentsForNinForm;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;

use Illuminate\Support\Str;

use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Screen\Fields\Picture;

class StudentListScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        return [
            'students' => Student::with('district')
                ->filters()
                ->latest()
                ->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Student Management';
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
    public function commandBar(): array
    {
        return [
            ModalToggle::make('Add New Student')
                ->modal('createStudentModal')
                ->method('save')
                ->icon('plus'),



            ModalToggle::make('Import Students')
                ->modal('uploadStudentsModal')
                ->method('upload')
                ->icon('upload')
                ->canSee(auth()->user()->hasAccess('platform.students.import')),

            Button::make('Export Data')
                ->method('download')
                ->rawClick(false)
                ->canSee(auth()->user()->hasAccess('platform.students.export'))
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
            Layout::rows([

                Group::make([
                    // Select::make('institution_id')
                    //     ->fromModel(Institution::class, 'institution_name')
                    //     ->title('Filter By Institution Name')
                    //     ->empty('Non Selected'),

                    Relation::make('institution_id')
                        ->title('Select Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->chunk(20),

                    Input::make('name')
                        ->title('Filter By Name'),

                    Relation::make('district_id')
                        ->fromModel(District::class, 'district_name')
                        ->title('Filter By District of origin'),

                    Select::make('gender')
                        ->title('Filter By Gender')
                        ->options([
                            'Male' => 'Male',
                            'Female' => 'Female'
                        ])
                        ->empty('Not Selected')
                ]),
                Group::make([
                    Button::make('Submit')
                        ->method('filter'),

                    // Reset Filters
                    Button::make('Reset')
                        ->method('reset')

                ])->autoWidth()
                    ->alignEnd(),
            ])->title("Filter Students"),

            Layout::table('students', [
                TD::make('id', 'ID'),
                // Show passport picture
                TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district.district_name', 'District'),
                TD::make('country', 'Country'),
                TD::make('location', 'Location'),
                TD::make('identifier', 'Identifier')->render(fn(Student $student) => $student->identifier),
                TD::make('nsin', 'NSIN')->render(fn(Student $student) => $student->nsin),
                TD::make('telephone', 'Phone Number'),
                TD::make('email', 'Email')->defaultHidden(),
                // TD::make('old', __('Old Student'))
                //     ->render(function ($student) {
                //         if ($student->flag === 1) {
                //             return __('Yes'); // You can replace 'Yes' with your custom label
                //         } else {
                //             return __('No'); // You can replace 'No' with your custom label
                //         }
                //     }),
                TD::make('date_time', 'Registration Date'),
                // TD::make('district.district_name', 'District'),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn(Student $student) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([
                            ModalToggle::make('Details')
                                ->icon('bs.people')
                                ->modal('asyncViewStudentModal')
                                ->modalTitle('Student Profile')
                                ->asyncParameters([
                                    'student' => $student->id
                                ]),

                            ModalToggle::make('Edit')
                                ->icon('bs.people')
                                ->modal('editStudentModal')
                                ->modalTitle('Edit Student Profile')
                                ->method('edit')
                                ->asyncParameters([
                                    'student' => $student->id
                                ]),

                            Button::make(__('Remove'))
                                ->icon('bs.trash3')
                                ->confirm(__('Are you sure, you want to remove this student record.'))
                                ->method('remove', [
                                    'id' => $student->id,
                                ]),
                        ])),
            ]),



            Layout::modal('createStudentModal', AddNewStudentForm::class)
                ->size(Modal::SIZE_LG)
                ->title('Create Student')
                ->applyButton('Create Student'),

            Layout::modal('uploadStudentsModal', Layout::rows([
                Link::make('Download Bulk Student Upload Template')
            ])),

            Layout::modal('editStudentModal', Layout::rows([

                Picture::make('student.passport')
                    ->title('Provide Student Photo')
                    ->type('file')
                    ->name('student.passport')
                    ->placeholder('Enter student passport photo')
                    ->width(270)
                    ->height(270)
                    ->required(),

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
                        ->required(),
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
                    Select::make('student.district_id')
                        ->title('District')
                        ->fromModel(District::class, 'district_name')
                        ->empty('Non Selected')
                        ->required(),

                    Input::make('student.location')
                        ->title('Address')
                        ->placeholder('Enter student address')
                        ->required(),
                ]),

                Group::make([


                    Input::make('student.nin')
                        ->title('National Identification Number / Passport Number')
                        ->required(),

                ]),

            ]))->async('asyncGetStudent')
                ->size(Modal::SIZE_LG),

            Layout::modal('asyncViewStudentModal', Layout::columns([
                Layout::view('student_profile', [
                    'student' => null
                ])
            ]))
                ->size(Modal::SIZE_LG)
                ->async('asyncGetStudent'),
        ];
    }

    public function asyncGetStudent(Student $student): iterable
    {
        return [
            'student' => $student,
        ];
    }

    public function saveStudent(Request $request, Student $student): void
    {
        // $request->validate([
        //     'students.email' => [
        //         'required',
        //         Rule::unique(Student::class, 'email')->ignore($student),
        //     ],
        //     'students.nin' => 'required_without_all:students.lin,students.passport_number',
        //     'students.lin' => 'required_without_all:students.nin,students.passport_number',
        //     'students.passport_number' => 'required_without_all:students.nin,students.lin',
        // ]);

        $request->validate([
            'students.email' => [
                'required',
                Rule::unique(Student::class, 'email')->ignore($student),
            ],
            'students.dob' => [
                'required',
                function ($attribute, $value, $fail) {
                    $age = Carbon::parse($value)->age;
                    if ($age < 18) {
                        $fail('The student must be at least 18 years old.');
                    }
                },
            ],
            'students.nin' => [
                'nullable',
                'string',
            ],
            'students.lin' => [
                'nullable',
                'string',
            ],
            'students.passport_number' => [
                'nullable',
                'string',
            ],
            'students.nin_or_lin_or_passport' => [
                function ($attribute, $value, $fail) use ($request) {
                    $nin = $request->input('students.nin');
                    $lin = $request->input('students.lin');
                    $passportNumber = $request->input('students.passport_number');

                    if (empty($nin) && empty($lin) && empty($passportNumber)) {
                        $fail('At least one of NIN, LIN, or Passport Number is required.');
                    }
                },
            ],
        ]);


        $student->fill($request->input('students'))->save();

        Alert::info(__('Student was saved.'));
    }

    public function remove(Request $request): void
    {
        Student::findOrFail($request->get('id'))->delete();

        Alert::info(__('Student was removed.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function save(Request $request)
    {
        $request->validate([
            'student.surname' => 'required',
            'student.firstname' => 'required',
            'student.gender' => 'required',
            'student.dob' => 'required',
            'student.passport' => 'required',
            'student.telephone' => 'required',
            'student.email' => 'required',
            'student.district_id' => 'required',
            'student.nin' => 'required',
            'student.lin' => 'required',
            'student.passport_number' => 'required',
        ]);

        $student = null;

        $previousNSIN = $request->input('previous_nsin');

        if ($previousNSIN != null) {
            $student = Student::firstWhere('nsin', $previousNSIN);
            $student->nsin = null;
        } else {
            $student = new Student();
            $student->nsin = null;
        }

        $student->firstname = $request->input('student.firstname');
        $student->surname = $request->input('student.surname');
        $student->othername = $request->input('student.othername');
        $student->dob = $request->input('student.dob');
        $student->gender = $request->input('student.gender');
        $student->district_id = $request->input('student.district_id');
        $student->telephone = $request->input('student.telephone');
        $student->email = $request->input('student.email');
        $student->date_time = now();
        $student->nin = $request->input('student.nin');
        $student->institution_id = $request->input('institution_id');
        $student->passport = $request->input('student.passport');
        $student->location = $request->input('student.location');

        $student->save();

        Alert::success("Student record uploaded");

        return redirect()->back();
    }



    public function currentUser(): User
    {
        return auth()->user();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function filter(Request $request)
    {

        $institutionId = $request->input('institution_id');
        $name = $request->input('name');
        $gender = $request->input('gender');
        $district = $request->input('district_id');

        $filterParams = [];

        if (!empty ($institutionId)) {
            $filterParams['filter[institution_id]'] = $institutionId;
        }

        if (!empty ($name)) {
            $filterParams['filter[name]'] = $name;
        }

        if (!empty ($gender)) {
            $filterParams['filter[gender]'] = $gender;
        }

        if (!empty ($district)) {
            $filterParams['filter[district_id]'] = $district;
        }

        $url = route('platform.students', $filterParams);

        return redirect()->to($url);
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function reset(Request $request)
    {
        return redirect()->route('platform.students');
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function download(Request $request)
    {
        return Excel::download(new StudentExport, 'students.csv', ExcelExcel::CSV);
    }

    public function edit(Request $request, Student $student)
    {
        $student->fill($request->input('student'))->save();

        Alert::success('Student record updated');
    }
}
