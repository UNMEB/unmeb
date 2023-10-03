<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\ExamRegistration;
use App\Models\Institution;
use App\Models\Student;
use App\Models\SurchargeFee;
use App\Models\Transaction;
use App\Models\User;
use App\Orchid\Layouts\RegisterExamFormListener;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Fields\Select;
use Orchid\Screen\Layouts\Modal;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;
use Orchid\Support\Facades\Toast;
use Illuminate\Support\Str;
use Orchid\Screen\TD;

class ExamRegistrationListScreen extends Screen
{
    public $status;

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query($status = NULL): iterable
    {

        $query = ExamRegistration::with('institution', 'course', 'student', 'examRegistrationPeriod');
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
            'registrations' => $query->paginate(),
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
        $name = ($status == 'incomplete') ? 'Incomplete Exam Registration' : (($status == 'accepted') ? 'Accepted Exam Registration' : (($status == 'rejected') ? 'Rejected Exam Registrations' : 'Exam Registrations'));
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
            ModalToggle::make('Register Students')
                ->modal('registerStudentsModal')
                ->method('register')
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
            // Import Students
            Layout::modal('registerStudentsModal', RegisterExamFormListener::class)
                ->title('Register Students')
                ->applyButton('Register Students'),




            Layout::table('registrations', [
                TD::make('exam_registration_period_id', 'Registration Period')->render(function (ExamRegistration $data) {
                    $registration = $data->examRegistrationPeriod;
                    return $registration->start_date . '/' . $registration->end_date;
                }),
                TD::make('course_id', 'Course')->render(fn ($data) => $data->course->name),
                TD::make('student_id', 'Student')->render(fn ($data) => $data->student->fullName),
                TD::make('number_of_papers', 'Number of papers'),
                TD::make('course_codes', 'Course Coded'),
                TD::make('trial', 'Trial'),
                TD::make('study_period', 'Study Period'),
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
    public function register(Request $request)
    {
        $examRegistrationPeriodId = $request->input('exam_registration_period_id');
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');

        $paperIds = $request->input('paper_ids');

        $studentIds = $request->input('student_ids');

        $feePerStudent = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
            ->select('surcharge_fees.surcharge_id', 'surcharges.name AS surcharge_name', 'surcharge_fees.course_fee')
            ->where('surcharge_fees.course_id', $courseId)
            ->where('surcharges.is_active', 1)
            ->first()
            ->course_fee;

        $feeStudents = count($studentIds) * $feePerStudent;

        // Check if account balance is enough to register all students
        $institution = Institution::find($institutionId);

        $accountBalance = $institution->account->balance;

        if ($accountBalance < $feeStudents) {

            Alert::error("Account balance is not enough to register all students.");

            return back()->with('error', 'Account balance is not enough to register all students.');
        }

        // Students & Attemps
        $studentAttempts = [];

        // Register Each Student Individually
        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);

            $studentExamRegistrations = $student->examRegistrations()->where('exam_registration_period_id', $examRegistrationPeriodId)->get();

            dd($studentExamRegistrations);
        }
    }



    public function currentUser(): User
    {
        return Auth()->user();
    }
}
