<?php

namespace App\Orchid\Screens\Student;

use App\Exports\StudentExport;
use App\Imports\StudentImport;
use App\Jobs\NotifyUserOfCompletedImport;
use App\Jobs\StudentImportJob;
use App\Models\District;
use App\Models\Student;
use App\Orchid\Layouts\RegisterStudentFormListener;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;
use Orchid\Attachment\File;
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
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

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
                ->defaultSort('id', 'desc')
                ->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Manage Students';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add New Student')
                ->modal('createStudentModal')
                ->method('create')
                ->icon('plus'),

            ModalToggle::make('Register Student')
            ->modal('registerStudentModal')
            ->method('register')
                ->icon('plus'),

            ModalToggle::make('Import Students')
                ->modal('uploadStudentsModal')
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
            Layout::table('students', [
                TD::make('id', 'ID')
                    ->width('100'),
                TD::make('photo', 'Photo')
                    ->width('100')
                    ->render(function (Student $student) {
                        // Get the first attachment for the student
                        $attachment = $student->attachment()->first();

                        // Check if an attachment exists
                        if ($attachment) {
                            // Display the student's image with a maximum width of 100px
                            return "<img src='" . $attachment->url() . "' alt='" . $student->name . "' style='max-width: 50px;'>";
                        }

                        // If no attachment is found, return a placeholder avatar image from the public directory
                        $placeholderUrl = asset('placeholder/avatar.png'); // Adjust the path to your placeholder image
                        return "<img src='" . $placeholderUrl . "' alt='Placeholder Avatar' style='max-width: 50px;'>";

                    }),
                TD::make('nsin', _('NSIN')),
                TD::make('name', _('Name'))->render(fn ($data) => $data->fullName),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date Of Birth'),
                TD::make('district_id', 'District')->render(function (Student $student) {
                    return optional($student->district)->name;
                }),
                TD::make('country', 'Country'),
                TD::make('address', 'Address'),
                TD::make('telephone', 'Telephone'),
                TD::make('email', 'Email Address'),

                TD::make('old_student', _('Old Student'))
                    ->render(function ($student) {
                        if ($student->flag === 1) {
                            return __('Yes'); // You can replace 'Yes' with your custom label
                        } else {
                            return __('No'); // You can replace 'No' with your custom label
                        }
                    }),

                TD::make('created_at', __('Created On'))
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->defaultHidden()
                    ->sort(),

                TD::make('updated_at', __('Last Updated'))
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
                    ->defaultHidden()
                    ->sort(),

                TD::make(__('Actions'))
                    ->width(200)
                    ->cantHide()
                    ->align(TD::ALIGN_CENTER)
                    ->render(function (Student $student) {
                        $editButton = ModalToggle::make('Edit Student')
                            ->modal('editStudentModal')
                            ->modalTitle('Edit Student ' . $student->name)
                            ->method('edit') // You can define your edit method here
                            ->asyncParameters([
                                'student' => $student->id,
                            ])
                            ->render();

                        $deleteButton = Button::make('Delete')
                            ->confirm('Are you sure you want to delete this student?')
                            ->method('delete', [
                                'id' => $student->id
                            ])
                            ->render();

                        return "<div style='display: flex; justify-content: space-between;'>$editButton  $deleteButton</div>";
                    })
            ]),
            Layout::modal('createStudentModal', Layout::rows([

                Group::make([
                    Input::make('student.nsin')
                        ->title('Student NSIN')
                        ->placeholder('Enter student nsin'),

                    Select::make('student.district_id')
                        ->title('District')
                        ->fromModel(District::class, 'name')
                        ->empty('No Districts'),

                ]),

                Group::make([
                    Input::make('student.surname')
                        ->title('Surname')
                        ->placeholder('Enter Surname'),

                    Input::make('student.firstname')
                        ->title('First Name')
                        ->placeholder('Enter First name'),

                    Input::make('student.othername')
                        ->title('Other Name')
                        ->placeholder('Enter Other name'),
                ]),

                Group::make([
                    Select::make('student.gender')
                        ->options([
                            'male' => 'MALE',
                            'female' => 'FEMALE',
                            'other' => 'OTHER'
                        ])
                        ->title('Student Gender'),


                    Input::make('student.dob')
                        ->title('Date Of Birth')
                        ->type('date')
                        ->placeholder('Enter date of birth'),
                ]),

                Group::make([
                    Input::make('student.telephone')
                        ->title('Phone Number')
                        ->placeholder('Enter phone number'),
                    Input::make('student.email')
                        ->title('Student Email Address')
                        ->placeholder('Enter email address'),
                ]),

                Input::make('student.passport')
                ->title('Provide Passport Photo')
                ->type('file')
                ->name('student.passport')
                ->placeholder('Enter student passport photo'),


            ]))
                ->size(Modal::SIZE_LG)
                ->title('Create Student')
                ->applyButton('Create Student'),

            Layout::modal('editStudentModal', Layout::rows([
                Input::make('student.name')
                    ->type('text')
                    ->title('Student Name')
                    ->help('Student e.g 2012')
                    ->horizontal(),

                Select::make('student.flag')
                    ->options([
                        1  => 'Active',
                        0  => 'Inactive',
                    ])
                    ->title('Flag')
                    ->help('Status for Active/Inactive student flag')
                    ->horizontal()
                    ->empty('No select')
            ]))->async('asyncGetStudent'),

            Layout::modal('uploadStudentsModal', Layout::rows([
                Input::make('file')
                    ->type('file')
                    ->title('Import Students'),
            ]))
                ->title('Upload Students')
                ->applyButton('Upload Students'),


            Layout::modal('registerStudentModal',  RegisterStudentFormListener::class),



        ];
    }

    /**
     * Get the number of models to return per page
     *
     * @return int
     */
    public static function perPage(): int
    {
        return 30;
    }

    /**
     * @return array
     */
    public function asyncGetStudent(Student $student): iterable
    {
        return [
            'student' => $student,
        ];
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function create(Request $request)
    {
        $request->validate(['student.surname' => 'required',
            'student.othername' => 'required',
            'student.firstname' => 'required',
            'student.gender' => 'required',
            'student.dob' => 'required',
            'student.passport' => 'required|file',
            'student.telephone' => 'required',
            'student.email' => 'required',
            'student.district_id' => 'required',
            'student.nsin' => 'required'
        ]);

        // Check if a file was uploaded
        if ($request->hasFile('student.passport')) {
            // Get the uploaded file
            $uploadedFile = $request->file('student.passport');

            // Define the directory to store uploaded files
            $photoDirectory = public_path('photos');

            // Generate a unique filename for the photo
            $photoFilename = uniqid() . '.' . $uploadedFile->getClientOriginalExtension();

            // Move the uploaded file to the destination directory
            $uploadedFile->move($photoDirectory, $photoFilename);

            // Save the file path to the database
            $photoPath = $photoDirectory . '/' . $photoFilename;

        // Create a new Student record and set its attributes
        $student = new Student();
            $student->firstname = $request->input('student.firstname');
            $student->surname = $request->input('student.surname');
            $student->othername = $request->input('student.othername');
            $student->nsin = $request->input('student.nsin');
            $student->dob = $request->input('student.dob');
            $student->gender = $request->input('student.gender');
            $student->district_id = $request->input('student.district_id');
            $student->telephone = $request->input('student.telephone');
            $student->email = $request->input('student.email');
        $student->name = $request->input('student.name');
            $student->photo = $photoFilename; // Store the filename in the database
        $student->save();

        Alert::success("Student was created");
        } else {
            // Handle the case where no file was uploaded
            Alert::error("Passport photo is required");
        }
    }


    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function edit(Request $request, Student $student): void
    {
        $request->validate([
            'student.name'
        ]);

        $student->fill($request->input('student'))->save();

        Alert::info(__('Student was updated.'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function delete(Request $request): void
    {
        Student::findOrFail($request->get('id'))->delete();

        Alert::success("Student was deleted.");
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
            'file' => 'required|file|mimes:csv|max:64000', // 64MB in kilobytes
            // Add any other validation rules you need for other fields
        ], $customMessages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Retrieve the uploaded file from the request
        $uploadedFile = $request->file('file');

        // Generate a unique filename for the uploaded file
        $filename = time() . '_' . $uploadedFile->getClientOriginalName();

        // Get the public path where the file will be stored
        $publicPath = public_path('storage/' . $filename);

        // Store the file in the public directory
        $uploadedFile->storeAs('public', $filename);

        // Start the StudentImportJob here
        dispatch(new StudentImportJob($publicPath));

        // Display a success message using SweetAlert
        Alert::success("Student data imported successfully");

        // Data import was successful
        return redirect()->back()->with('success', 'Students data imported successfully.');
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
}
