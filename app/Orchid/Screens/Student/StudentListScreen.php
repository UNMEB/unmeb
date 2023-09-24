<?php

namespace App\Orchid\Screens\Student;

use App\Exports\StudentExport;
use App\Imports\StudentImport;
use App\Models\District;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Excel as ExcelExcel;
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
            'students' => Student::with('district')->latest()->get()
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
            ModalToggle::make('Add Student')
                ->modal('createStudentModal')
                ->method('create')
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
                TD::make('surname', _('Surname')),
                TD::make('firstname', _('First Name')),
                TD::make('othername', _('Other Name')),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date Of Birth'),
                TD::make('district_id', 'District')->render(function (Student $student) {
                    return $student->district->name;
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
                    ->sort(),

                TD::make('updated_at', __('Last Updated'))
                    ->usingComponent(DateTimeSplit::class)
                    ->align(TD::ALIGN_RIGHT)
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

                Group::make([
                    Input::make('student.photo')
                        ->title('Provide Passport Photo')
                        ->type('file')
                        ->name('student.photo')
                        ->placeholder('Enter student passport photo'),

                    Input::make('student.national_id')
                        ->title('National ID')
                        ->type('file')
                        ->name('student.national_id')
                        ->help('Attach Stident national ID'),

                    Input::make('student.passport')
                        ->title('Passport')
                        ->type('file')
                        ->name('student.passport')
                        ->help('Attach student passport'),
                ]),
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

        ];
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
        $request->validate([
            'student.name' => 'required|numeric'
        ]);

        $student = new Student();
        $student->name = $request->input('student.name');
        $student->save();

        Alert::success("Student was created");
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

        // Use Laravel Excel to import the data using your custom importer
        try {
            // Get the path of the uploaded file
            $filePath = $uploadedFile->path();

            // Import the data using your custom importer
            Excel::import(new StudentImport, $filePath);

            // Display a success message using SweetAlert
            Alert::success("Student data imported successfully");

            // Data import was successful
            return redirect()->back()->with('success', 'Students data imported successfully.');
        } catch (\Exception $e) {
            // Handle any exceptions that may occur during import
            Alert::error($e->getMessage());

            return redirect()->back()->with('error', 'An error occurred during import: ' . $e->getMessage());
        }
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
