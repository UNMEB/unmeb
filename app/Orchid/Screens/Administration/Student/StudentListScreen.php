<?php

namespace App\Orchid\Screens\Administration\Student;

use App\Exports\StudentExport;
use App\Imports\StudentImport;
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
use App\Orchid\Layouts\AddStudentWithNSINForm;
use App\Orchid\Layouts\ExportStudentPictureForm;
use App\Orchid\Layouts\RegisterStudentsForNinForm;
use Carbon\Carbon;
use Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Validators\ValidationException;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Cropper;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Fields\Upload;
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
use ZipArchive;

class StudentListScreen extends Screen
{

    public $filters = [];

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $this->filters = $request->get("filter");

        $query = Student::withoutGlobalScopes()
            ->with('district')
            ->select([
                's.id as id',
                's.surname',
                's.firstname',
                's.othername',
                's.gender',
                's.dob',
                's.district_id',
                's.country_id',
                's.location',
                's.nsin as nsin',
                's.passport_number',
                's.nin',
                's.telephone',
                's.refugee_number',
                's.lin',
                's.date_time',
                's.passport'
            ])
            ->from('students As s')
            ->orderBy('s.created_at', 'desc');

        if (auth()->user()->inRole('institution')) {
            $query->where('s.institution_id', auth()->user()->institution_id);
        }

        if (!empty($this->filters)) {
            if (isset($this->filters['institution_id']) && $this->filters['institution_id'] !== null) {
                $institutionId = $this->filters['institution_id'];
                $query->where('s.institution_id', '=', $institutionId);
            }

            if (isset($this->filters['district_id']) && $this->filters['district_id'] !== null) {
                $districtId = $this->filters['district_id'];
                $query->where('s.district_id', '=', $districtId);
            }

            if (isset($this->filters['name']) && $this->filters['name'] !== null) {
                $name = $this->filters['name'];
                $terms = explode(' ', $name);
                if (count($terms) == 2) {
                    [$firstTerm, $secondTerm] = $terms;
                    $query->where(function ($query) use ($firstTerm, $secondTerm) {
                        $query->where('firstname', 'like', '%' . $firstTerm . '%')
                            ->where('surname', 'like', '%' . $secondTerm . '%');
                    })->orWhere(function ($query) use ($firstTerm, $secondTerm) {
                        // Check if the first term matches the surname and the second term matches the firstname
                        $query->where('surname', 'like', '%' . $firstTerm . '%')
                            ->where('firstname', 'like', '%' . $secondTerm . '%');
                    });
                }

                $query->where('firstname', 'like', '%' . $name . '%')
                    ->orWhere('surname', 'like', '%' . $name . '%')
                    ->orWhere('othername', 'like', '%' . $name . '%');
            }

            if (isset($this->filters['gender']) && $this->filters['gender'] !== null) {
                $gender = $this->filters['gender'];
                $query->where('s.gender', '=', $gender);
            }
        }

        return [
            'students' => $query->paginate(),
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
                ->icon('plus')
                ->class('btn btn-default btn-success'),


            ModalToggle::make('Add Student With NSIN')
                ->modal('createStudentWithNsinModal')
                ->method('saveWithNSIN')
                ->icon('plus')
                ->class('btn btn-default btn-success'),

            ModalToggle::make('Import Students')
                ->modal('uploadStudentsModal')
                ->method('upload')
                ->icon('upload')
                ->modalTitle('Import students in bulk')
                ->canSee(auth()->user()->hasAccess('platform.students.import'))
                ->class('btn btn-success'),

            Button::make('Export Data')
                ->method('download')
                ->rawClick(false)
                ->canSee(auth()->user()->hasAccess('platform.students.export'))
                ->class('btn btn-primary'),

            ModalToggle::make('Export Pictures')
                ->modal('exportPicturesModal')
                ->modalTitle('Export Student Pictures')
                ->method('exportPictures')
                ->canSee(auth()->user()->hasAccess('platform.students.export'))
                ->class('btn btn-primary'),

            DropDown::make()
                ->icon('bs.three-dots-vertical')
                ->list([
                    ModalToggle::make('Rollback Action')
                ])->canSee(auth()->user()->inRole('administrator'))
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

            Layout::modal('exportPicturesModal', ExportStudentPictureForm::class),
            Layout::rows([

                Group::make([
                    Relation::make('institution_id')
                        ->title('Select Institution')
                        ->fromModel(Institution::class, 'institution_name')
                        ->applyScope('userInstitutions')
                        ->canSee(!auth()->user()->inRole('institution'))
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

            Layout::view('table_summary'),

            Layout::table('students', [
                TD::make('id', 'ID'),
                // Show passport picture
                TD::make('avatar', 'Passport')->render(fn(Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district.district_name', 'District'),
                TD::make('country_id', 'Country')->render(fn(Student $student) => optional($student->country)->name),
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

            Layout::modal('createStudentWithNsinModal', AddStudentWithNSINForm::class)
                ->size(Modal::SIZE_LG)
                ->title('Save Student With NSIN')
                ->applyButton('Save Student Info'),

            Layout::modal('uploadStudentsModal', [

                Layout::rows([
                    Input::make('file')
                        ->type('file')
                        ->help("Import excel file containing student data")
                ]),

                Layout::accordion([
                    'Import Instructions' => Layout::view('import_instructions'),
                ]),
            ])->applyButton("Import Students"),

            Layout::modal('editStudentModal', Layout::rows([

                Picture::make('student.passport')
                    ->title('Provide Student Photo')
                    ->type('file')
                    ->name('student.passport')
                    ->placeholder('Enter student passport photo')
                    ->width(270)
                    ->height(270)
                    ->required()
                    ->targetRelativeUrl(),

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
        $request->validate([
            'students.dob' => [
                'required',
                function ($attribute, $value, $fail) {
                    $age = Carbon::parse($value)->age;
                    if ($age < 18) {
                        $fail('The student must be at least 18 years old.');
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
            'student.passport' => 'required',
            'student.telephone' => 'required',
            'student.district_id' => 'required',
            'student.applied_program' => 'required',
            'student.country_id' => 'required',
            'student.dob' => [
                'required',
                function ($attribute, $value, $fail) {
                    $age = Carbon::parse($value)->age;
                    if ($age < 18) {
                        $fail('The student must be at least 18 years old.');
                    }
                },
            ],
        ]);

        $institutionId = auth()->user()->inRole('institution') ? auth()->user()->institution_id : $request->input('student.institution_id');

        $student = null;

        $previousNSIN = $request->input('previous_nsin');

        if ($previousNSIN != null) {
            $student = Student::firstWhere('nsin', $previousNSIN);

            // Check if $student is not null before accessing its properties
            if ($student != null) {
                $student->nsin = null;
            } else {
                // If $student is null, create a new instance
                $student = new Student();
            }
        } else {
            $student = new Student();
        }

        // Assign other properties
        $student->firstname = $request->input('student.firstname');
        $student->surname = $request->input('student.surname');
        $student->othername = $request->input('student.othername');
        $student->dob = $request->input('student.dob');
        $student->gender = $request->input('student.gender');
        $student->district_id = $request->input('student.district_id');
        $student->country_id = $request->input('student.country_id');
        $student->telephone = $request->input('student.telephone');
        $student->email = $request->input('student.email');
        $student->date_time = now();
        $student->nin = $request->input('student.nin');
        $student->passport_number = $request->input('student.passport_number');
        $student->lin = $request->input('student.lin');
        $student->institution_id = $institutionId;
        $student->passport = $request->input('student.passport');
        $student->location = $request->input('student.location');
        $student->applied_program = $request->input('student.applied_program');

        // Save the student
        $student->save();

        \RealRashid\SweetAlert\Facades\Alert::success('Action Complete', 'Student records saved.');

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

        if (!empty($institutionId)) {
            $filterParams['filter[institution_id]'] = $institutionId;
        }

        if (!empty($name)) {
            $filterParams['filter[name]'] = $name;
        }

        if (!empty($gender)) {
            $filterParams['filter[gender]'] = $gender;
        }

        if (!empty($district)) {
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
        (new StudentExport(auth()->user()->institution_id))->queue('students.csv', ExcelExcel::CSV);

        Toast::success('Student list is being exported. Please wait');

        return back()->withSuccess('Export started!');
    }

    public function upload(Request $request)
    {

        if (!$this->currentUser()->inRole('institution')) {
            \RealRashid\SweetAlert\Facades\Alert::error('Student Upload Failed', 'Only institutions can upload student info. Please switch to institution account')
                ->autoClose(15000)
            ;
            return back();
        }

        $customMessages = [
            'file.required' => 'Please select a file to upload.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'The file must be an excel document.',
            'file.max' => 'The file size must not exceed 64MB.',
        ];

        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'file' => 'required|file|mimes:xls,xlsx|max:128000',
        ], $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $uploadedFile = $request->file('file');

        $filePath = $uploadedFile->path();

        try {
            Excel::import(new StudentImport, $filePath, null, ExcelExcel::XLSX);
        } catch (ValidationException $e) {
            $failures = $e->failures();

            $errorMessages = collect($failures)->map(function ($failure) {
                return $failure->errors()[0];
            })->toArray();

            // Now you can handle these error messages. For example, you can log them or show them to the user.
            // For demonstration, let's flash the error messages to the session and redirect back with an alert.
            return redirect()->back()->withInput()->withErrors($errorMessages)->with('error', 'There were validation errors during the import process.');
        }



    }

    public function edit(Request $request, Student $student)
    {
        $student->fill($request->input('student'))->save();

        Alert::success('Student record updated');
    }

    public function saveWithNSIN(Request $request)
    {
        $request->validate([
            'student.surname' => 'required',
            'student.firstname' => 'required',
            'student.gender' => 'required',
            'student.passport' => 'required',
            'student.telephone' => 'required',
            'student.district_id' => 'required',
            'student.applied_program' => 'required',
            'student.country_id' => 'required',
            'student.nsin' => 'required',
            'student.dob' => [
                'required',
                function ($attribute, $value, $fail) {
                    $age = Carbon::parse($value)->age;
                    if ($age < 18) {
                        $fail('The student must be at least 18 years old.');
                    }
                },
            ],
        ]);

        $institutionId = auth()->user()->inRole('institution') ? auth()->user()->institution_id : $request->input('student.institution_id');

        $student = new Student();

        // Assign other properties
        $student->firstname = $request->input('student.firstname');
        $student->surname = $request->input('student.surname');
        $student->othername = $request->input('student.othername');
        $student->dob = $request->input('student.dob');
        $student->gender = $request->input('student.gender');
        $student->district_id = $request->input('student.district_id');
        $student->country_id = $request->input('student.country_id');
        $student->telephone = $request->input('student.telephone');
        $student->email = $request->input('student.email');
        $student->date_time = now();
        $student->nin = $request->input('student.nin');
        $student->passport_number = $request->input('student.passport_number');
        $student->lin = $request->input('student.lin');
        $student->institution_id = $institutionId;
        $student->passport = $request->input('student.passport');
        $student->location = $request->input('student.location');
        $student->applied_program = $request->input('student.applied_program');
        $student->nsin = $request->input('student.nsin');


        // Save the student
        $student->save();

        \RealRashid\SweetAlert\Facades\Alert::success('Action Complete', 'Student records saved.');

        return redirect()->back();
    }

    public function exportPictures(Request $request)
    {
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');

        $courseName = Course::find($courseId)->first()->course_name;

        // Get list of student pictures
        $students = Student::whereNotNull('passport')
            ->from('students as s')
            ->join('nsin_student_registrations as nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id')
            ->join('nsin_registration_periods AS nrp', function ($join) {
                $join->on('nrp.month', '=', 'nr.month');
                $join->on('nrp.year_id', '=', 'nr.year_id');
            })
            ->where('nr.institution_id', $institutionId)
            ->where('nr.course_id', $courseId)
            ->whereNotNull('s.nsin')
            ->get();

        // Create a zip archive
        $zip = new ZipArchive;
        $zipFileName = Str::slug($courseName) . '_student_pictures.zip';
        $zipFilePath = storage_path($zipFileName);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Add files to the zip file
            foreach ($students as $student) {
                if (filter_var($student->passport, FILTER_VALIDATE_URL)) {
                    $response = Http::get($student->passport);
                    if ($response->successful()) {
                        $fileName = str_replace('/', '-', $student->nsin) . '.' . pathinfo($student->passport, PATHINFO_EXTENSION);
                        $zip->addFromString($fileName, $response->body());
                    }
                }
            }
            // Close the zip file
            $zip->close();

            // Print out the link to the zip file
            $link = asset('storage/' . $zipFileName);
            // $this->info("Student pictures are zipped up. Download link: $link");

            \RealRashid\SweetAlert\Facades\Alert::success('Action Complete', "Click the link below to download the student pictures. <br> <a class='btn btn-link' href='$link'>Download Pictures</button>")
                ->toHtml()
                ->persistent(true);

        } else {
            \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', 'Unable to download pictures at the moment');
        }
    }
}
