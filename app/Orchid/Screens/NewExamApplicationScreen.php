<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Paper;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentPaperRegistration;
use App\Models\StudentRegistration;
use App\Models\SurchargeFee;
use App\Models\Transaction;
use App\Orchid\Layouts\RegisterStudentsForExamsTable;
use DB;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Support\Facades\Alert;

class NewExamApplicationScreen extends Screen
{
    public $institutionId;
    public $exam_registration_period_id;
    public $courseId;
    public $paperIds;
    public $yearOfStudy;
    public $trial;

    public function __construct(Request $request)
    {
        session()->flush();

        $this->institutionId = $request->get('institution_id') ?? null;
        $this->exam_registration_period_id = $request->get('exam_registration_period_id') ?? null;
        $this->courseId = $request->get('course_id') ?? null;
        $this->paperIds = $request->get('paper_ids') ?? null;
        $this->yearOfStudy = $request->get('year_of_study') ?? null;
        $this->trial = $request->get('trial') ?? null;

        session()->put('institution_id', $this->institutionId);
        session()->put('exam_registration_period_id', $this->exam_registration_period_id);
        session()->put('course_id', $this->courseId);
        session()->put('paper_ids', $this->paperIds);
        session()->put('year_of_study', $this->yearOfStudy);
        session()->put('trial', $this->trial);
    }


    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        // Retrieve institution, course, and exam period IDs from session
        $institutionId = session('institution_id');
        $courseId = session('course_id');
        $examPeriodId = session('exam_registration_period_id');

        $query = Student::withoutGlobalScopes()
            ->select('s.*')
            ->from('students AS s')
            ->join('nsin_student_registrations AS nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations AS nr', 'nr.id', '=', 'nsr.nsin_registration_id')
            ->join('institutions AS i', 'i.id', '=', 'nr.institution_id')
            ->join('courses AS c', 'c.id', '=', 'nr.course_id')
            ->leftJoin('student_registrations AS sr', function ($join) {
                $join->on('s.id', '=', 'sr.student_id')
                    ->where('sr.registration_id', '=', 'nsr.nsin_registration_id');
            })
            ->leftJoin('registrations AS r', 'r.id', '=', 'sr.registration_id')
            ->leftJoin('registration_periods AS rp', 'r.registration_period_id', '=', 'rp.id')
            ->where('s.institution_id', $institutionId)
            ->where('c.id', $courseId)
            ->whereNotNull('nsr.nsin')
            ->where('nsr.verify', 1)
            ->whereNull('r.id') // Exclude students who have exam registrations
            ->where('rp.id', $examPeriodId) // Filter by specific exam registration period
            ->orderBy('s.surname', 'asc')
            ->paginate(100);

        return [
            'students' => $query
        ];
    }


    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Register For Exams';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        return [
            RegisterStudentsForExamsTable::class,
        ];
    }

    public function submit(Request $request)
    {
        $totalCost = 0;
        $bill = 0;
        $examRegistrationPeriodId = session('exam_registration_period_id');
        $institutionId = session('institution_id');
        $courseId = session('course_id');
        $paperIds = session('paper_ids');
        $studentIds = collect($request->get('students'));
        $numberOfPapers = count($paperIds);
        $numberOfStudents = count($studentIds);
        $yearOfStudy = session('year_of_study');
        $trial = session('trial');
        $costPerPaper = config('settings.fees.paper_registration');

        // Get the institution
        $institution = Institution::find($institutionId);

        // Get the course
        $course = Course::find($courseId);

        // Account Balance
        $accountBalance = (float) $institution->account->balance;

        $normalCharge = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
            ->select('surcharge_fees.surcharge_id', 'surcharges.surcharge_name AS surcharge_name', 'surcharge_fees.course_fee')
            ->where('surcharge_fees.course_id', $courseId)
            ->where('surcharges.flag', 1)
            ->first();

        // Find or create the registration
        $registration = Registration::where([
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'year_of_study' => $yearOfStudy,
            'registration_period_id' => $examRegistrationPeriodId,
        ])->first();

        if (!$registration) {
            $registration = new Registration();
            $registration->institution_id = $institution->id;
            $registration->course_id = $courseId;
            $registration->amount = 0;
            $registration->year_of_study = $yearOfStudy;
            $registration->registration_period_id = $examRegistrationPeriodId;
            $registration->surcharge_id = $normalCharge->surcharge_id;
            $registration->save();
        }

        // Check bill for entire batch of students
        foreach ($studentIds as $studentId) {
            // if first attempt register normally
            if ($trial == 'First') {
                $bill += $normalCharge->course_fee;
            } else if ($trial == 'Second') {
                $costToPay = ($costPerPaper + ($costPerPaper * 0.5)) * count($paperIds);
                $bill += $costToPay;
            } else {
                $costToPay = ($costPerPaper + ($costPerPaper * 1)) * count($paperIds);
                $bill += $costToPay;
            }
        }

        // Check if bill exceeds account balance before registration
        if ($bill > $accountBalance) {
            \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', "Account balance too low to complete exam registration for all students. Please deposit funds to your account and try again.");
            return back();
        }

        // Proceed with registration for each student
        foreach ($studentIds as $studentId) {
            // if first attempt register normally
            if ($trial == 'First') {
                $amountToPay = $normalCharge->course_fee;
            } else if ($trial == 'Second') {
                $amountToPay = ($costPerPaper + ($costPerPaper * 0.5)) * count($paperIds);
            } else {
                $amountToPay = ($costPerPaper + ($costPerPaper * 1)) * count($paperIds);
            }

            $student = Student::find($studentId);

            // Register Student Exam Registration
            $existingRegistration = StudentRegistration::where([
                'registration_id' => $registration->id,
                'student_id' => $student->id,
                'trial' => $trial,
            ])->first();

            if (!$existingRegistration) {
                $courseCodes = Paper::whereIn('id', $paperIds)->pluck('code');

                $existingRegistration = new StudentRegistration();
                $existingRegistration->registration_id = $registration->id;
                $existingRegistration->trial = $trial;
                $existingRegistration->student_id = $student->id;
                $existingRegistration->course_codes = $courseCodes;
                $existingRegistration->no_of_papers = count($paperIds);
                $existingRegistration->sr_flag = 0;
                $existingRegistration->save();

                // Create a transaction for this student registration
                $transaction = new Transaction([
                    'amount' => $amountToPay, // Same bill amount for each student
                    'type' => 'debit',
                    'account_id' => $institution->account->id,
                    'institution_id' => $institution->id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'Exam Registration for student ID: ' . $student->id,
                ]);
                $transaction->save();

                // Update account balance and complete registration
                $newBalance = $institution->account->balance - $amountToPay;

                $institution->account->update([
                    'balance' => $newBalance,
                ]);

                // Register Student Papers
                $studentCoursePapers = DB::table('course_paper') // Use the actual pivot table name
                    ->where('course_id', $course->id)
                    ->whereIn('paper_id', $paperIds)
                    ->pluck('id');

                foreach ($studentCoursePapers as $coursePaperId) {
                    // Create a new StudentPaperRegistration record
                    $studentPaperRegistration = new StudentPaperRegistration();
                    $studentPaperRegistration->student_registration_id = $existingRegistration->id;
                    $studentPaperRegistration->course_paper_id = $coursePaperId;
                    $studentPaperRegistration->save();
                }

            }

        }

    }

}
