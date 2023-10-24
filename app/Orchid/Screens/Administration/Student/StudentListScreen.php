<?php

namespace App\Orchid\Screens\Administration\Student;

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
use App\Orchid\Layouts\RegisterStudentsForNinForm;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
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
                ->defaultSort('id', 'desc')
                ->orderBy('old', 'asc')
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
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Add New Student')
                ->modal('createStudentModal')
                ->method('save')
                ->icon('plus'),

            ModalToggle::make('Register Student For NSIN')
                ->modal('registerStudentModal')
                ->method('register')
                ->icon('plus'),

            ModalToggle::make('Import Students')
            ->modal('uploadStudentsModal')
            ->method('upload')
            ->icon('upload')
            ->canSee(auth()->user()->hasAccess('platform.administration.students.import')),

            Button::make('Export Data')
            ->method('download')
            ->rawClick(false)
            ->canSee(auth()->user()->hasAccess('platform.administration.students.export'))
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
                TD::make('id', 'ID'),
                // Show passport picture
                TD::make('avatar', 'Passport')->render(fn (Student $student) => $student->avatar),
                TD::make('fullName', 'Name'),
                TD::make('gender', 'Gender'),
                TD::make('dob', 'Date of Birth'),
                TD::make('district.district_name', 'District'),
                TD::make('country', 'Country'),
                TD::make('location', 'Location'),
                TD::make('nsin', 'NSIN'),
                TD::make('telephone', 'Phone Number'),
                TD::make('email', 'Email')->defaultHidden(),
                TD::make('old', __('Old Student'))
                    ->render(function ($student) {
                        if ($student->flag === 1) {
                            return __('Yes'); // You can replace 'Yes' with your custom label
                        } else {
                            return __('No'); // You can replace 'No' with your custom label
                        }
                    }),
                TD::make('date_time', 'Registration Date'),
                TD::make('district.district_name', 'District'),
                TD::make(__('Actions'))
                    ->align(TD::ALIGN_CENTER)
                    ->width('100px')
                    ->render(fn (Student $students) => DropDown::make()
                        ->icon('bs.three-dots-vertical')
                        ->list([

                            Link::make(__('Edit'))
                                ->route('platform.administration.students.edit', $students->id)
                                ->icon('bs.pencil'),

                            Button::make(__('Delete'))
                                ->icon('bs.trash3')
                                ->confirm(__('Once the account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.'))
                                ->method('remove', [
                                    'id' => $students->id,
                                ]),
                        ])),
            ]),

            Layout::modal('registerStudentModal', RegisterStudentsForNinForm::class)
                ->title('Register Students For NSIN'),

            Layout::modal('createStudentModal', Layout::rows([

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
                        'Male' => 'MALE',
                        'Female' => 'FEMALE',
                    ])
                        ->title('Student Gender')
                        ->empty('Non Selected'),


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
                    Select::make('student.district_id')
                    ->title('District')
                        ->fromModel(District::class, 'district_name')
                        ->empty('Non Selected'),

                    Input::make('student.location')
                    ->title('Address')
                        ->placeholder('Enter student address'),
                ]),

                Input::make('student.passport')
                ->title('Provide Passport Photo')
                ->type('file')
                    ->name('student.passport')
                    ->placeholder('Enter student passport photo'),


            ]))
                ->size(Modal::SIZE_LG)
                ->title('Create Student')
                ->applyButton('Create Student')
                ,

            Layout::modal('asyncEditStudentModal', Layout::rows([

            ]))->async('asyncGetStudent'),
        ];
    }

    public function asyncGetStudent(Student $students): iterable
    {
        return [
            'students' => $students,
        ];
    }

    public function saveStudent(Request $request, Student $students): void
    {
        $request->validate([
            'students.email' => [
                'required',
                Rule::unique(Student::class, 'email')->ignore($students),
            ],
        ]);

        $students->fill($request->input('students'))->save();

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
            'student.passport' => 'required|file',
            'student.telephone' => 'required',
            'student.email' => 'required',
            'student.district_id' => 'required'
        ]);

        $student = new Student();
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
        $student->date_time = now();

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

            $student->passport = $photoPath; // Store the filename in the database
            $student->save();

            Alert::success("Student record uploaded");

            return redirect()->back();
        }

        Alert::error("Passport photo is required");

        return  redirect()->back();
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return void
     */
    public function register(Request $request)
    {
        $nrpID = $request->get('nsin_registration_period_id');
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');
        $studentIds = $request->get('student_ids');

        $nsinRegistrationPeriod = NsinRegistrationPeriod::find($nrpID);

        $yearId = $nsinRegistrationPeriod->year_id;
        $month = $nsinRegistrationPeriod->month;

        $fee = 20000 * count($studentIds);

        $institution = Institution::find($institutionId);

        if ($fee > $institution->account->balance) {
            Alert::error('Account balance too low to complete this transaction. Please top up to continue');
            return back();
        }

        // Find the NSIN registration
        $nsinRegistration = NsinRegistration::where([
            'year_id' => $yearId,
            'month' => $month,
            'institution_id' => $institutionId,
            'course_id' => $courseId,
        ])->first();

        if ($nsinRegistration) {
            // Increment the amount
            $nsinRegistration->amount = $nsinRegistration->amount + $fee;

            // Save
            $nsinRegistration->save();
        } else {
            $nsinRegistration = new NsinRegistration();
            $nsinRegistration->year_id = $yearId;
            $nsinRegistration->month = $month;
            $nsinRegistration->institution_id = $institutionId;
            $nsinRegistration->course_id = $courseId;
            $nsinRegistration->amount = $fee;
            $nsinRegistration->save();
        }

        // For each student in the list create a NsinStudentRegistration if not already registered
        foreach ($studentIds as $studentId) {
            // Check if the student is already registered for the same period, institution, and course
            $existingRegistration = NsinStudentRegistration::where([
                'nsin_registration_id' => $nsinRegistration->id,
                'student_id' => $studentId,
                'verify' => 0
            ])->first();

            if (!$existingRegistration) {
                $nsinStudentRegistration = new NsinStudentRegistration();
                $nsinStudentRegistration->nsin_registration_id = $nsinRegistration->id;
                $nsinStudentRegistration->student_id = $studentId;
                $nsinStudentRegistration->verify = 0;
                $nsinStudentRegistration->save();
            }
        }

        // Create Transaction
        $newBalanace = $institution->account->balance - $fee;
        $institution->account->update([
            'balance' => $newBalanace,
        ]);

        $transaction = new Transaction([
            'amount' => $fee,
            'type' => 'debit',
            'account_id' => $institution->account->id,
            'institution_id' => $institution->id,
            'initiated_by' => auth()->user()->id,
            'is_approved' => 1,
            'comment'  => 'NSIN Registration',
        ]);

        $transaction->save();

        Alert::success('Registration successful');
    }

    public function currentUser(): User
    {
        return auth()->user();
    }

}
