<?php

namespace App\Orchid\Screens\Registration\Student;

use App\Imports\StudentImport;
use App\Models\Course;
use App\Models\Institution;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\StudentRegistrationPeriod;
use App\Models\Surcharge;
use App\Models\SurchargeFee;
use App\Models\Transaction;
use App\Models\User;
use App\Orchid\Layouts\RegisterExamFormListener;
use App\Orchid\Layouts\RegisterStudentFormListener;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Relation;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Illuminate\Support\Str;

class StudentRegistrationListScreen extends Screen
{
    public $status;
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query($status = null): iterable
    {
        $query = StudentRegistration::with('institution', 'course', 'student');

        if ($status != null) {
            $whereClause = [];
            if ($status == 'incomplete') {
                $whereClause = [
                    'is_completed' => 0,
                ];
            } else if ($status == 'accepted') {
                $whereClause = [
                    'is_completed' => 1,
                    'is_approved' => 1
                ];
            } else if ($status == 'rejected') {
                $whereClause = [
                    'is_completed' => 1,
                    'is_verified' => 0
                ];
            }

            $query->where($whereClause);
        }

        return [
            'status' => $status,
            'registration' => $query->paginate(),
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        $status = $this->status;
        $name = ($status == 'incomplete') ? 'Incomplete Student Registration' : (($status == 'accepted') ? 'Accepted Student Registration' : (($status == 'rejected') ? 'Rejected Student Registrations' : 'Student Registrations'));
        return $name;
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
                ->modal('addStudentsModal')
                ->method('add')
                ->icon('upload'),

            ModalToggle::make('Register for NSIN')
            ->modal('registerForNSINModal')
            ->modalTitle('Register Student For NSIN')
            ->method('add')
                ->icon('upload'),

            ModalToggle::make('Import Students')
                ->modal('importStudentsModal')
            ->modalTitle('Import Student Data')
                ->method('import')
            ->icon('upload'),
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
            Layout::modal('addStudentModal', Layout::rows([]))
                ->applyButton('Add New Student'),

            Layout::modal('registerForNSINModal', RegisterStudentFormListener::class),


            Layout::modal('importStudentsModal', Layout::rows([]))
                ->applyButton('Import Students'),


            Layout::table('registration', [
                TD::make('id', 'Registration ID'),
                TD::make('institution_id', 'Institution')->render(fn ($data) => $data->institution->name)
                    ->canSee($this->currentUser()->inRole('system-admin')),
                TD::make('course_id', 'Course')->render(fn ($data) => $data->course->name),
                TD::make('student_id', 'Student')->render(fn ($data) => optional($data->student)->fullName),
                TD::make('month', 'Month'),
                TD::make('year_id', 'Year')->render(function ($data) {
                    if (!empty($data->year_id) && $data->year_id > 0) {
                        return $data->year->name;
                    }

                    return null;
                }),
                TD::make('is_completed', 'Completed'),
                TD::make('is_approved', 'Approved'),
                TD::make('is_verified', 'Verified'),
                TD::make('remarks', 'Remarks'),
            ])
        ];
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
        (new StudentImport())->import($publicPath);

        // Display a success message using SweetAlert
        Alert::success("Student data imported successfully");

        // Data import was successful
        return redirect()->back()->with('success', 'Students data imported successfully.');
    }

    public function add(Request $request)
    {
        $registrationId = $request->input('student_registration_period_id');
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $studentIds = $request->input('student_ids');

        $institution = Institution::with('account')->find($institutionId);
        $course = Course::find($courseId);
        $registrationPeriod = StudentRegistrationPeriod::find($registrationId);

        $totalFee = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
        ->select('surcharge_fees.surcharge_id', 'surcharges.name AS surcharge_name', 'surcharge_fees.course_fee')
        ->where('surcharge_fees.course_id', $courseId)
            ->where('surcharges.is_active', 1)
            ->first()
            ->course_fee * count($studentIds);

        if ($institution->account->balance >= $totalFee) {
            foreach ($studentIds as $studentId) {
                $nsin = strtoupper(substr($registrationPeriod->month, 0, 3)) . substr($registrationPeriod->year->name, -2) . '/' . $course->code . '/' . $studentId;
                $student = Student::find($studentId);
                $student->nsin = $nsin;
                $student->save();

                $studentRegistration = new StudentRegistration([
                    'institution_id' => $institutionId,
                    'course_id' => $courseId,
                    'student_id' => $studentId,
                    'month' => $registrationPeriod->month,
                    'year_id' => $registrationPeriod->year_id,
                ]);
                $studentRegistration->save();
            }

            $newBalance = $institution->account->balance - $totalFee;
            $institution->account->update(['balance' => $newBalance]);

            $transaction = new Transaction([
                'amount' => $totalFee,
                'type' => 'debit',
                'is_approved' => 1,
                'account_id' => $institution->account->id,
                'institution_id' => $institution->id,
            ]);
            $transaction->save();

            Alert::success("Students registered successfully");
        } else {
            Alert::error("Account balance low. Please deposit funds into the institution account");
        }

        return redirect()->back();
    }


    public function currentUser(): User
    {
        return auth()->user();
    }

}
