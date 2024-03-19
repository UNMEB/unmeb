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
        $this->institutionId = $request->get('institution_id');
        $this->exam_registration_period_id = $request->get('exam_registration_period_id');
        $this->courseId = $request->get('course_id');
        $this->paperIds = $request->get('paper_ids');
        $this->yearOfStudy = $request->get('year_of_study');
        $this->trial = $request->get('trial');

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

        // Retrieve institution and course IDs from session
        $institutionId = session()->get('institution_id');
        $courseId = session()->get('course_id');

        // Your query
        $query = Student::withoutGlobalScopes()->leftJoin('student_registrations as sr', 'students.id', '=', 'sr.student_id')
            ->leftJoin('registrations as r', 'sr.registration_id', '=', 'r.id')
            ->leftJoin('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->leftJoin('courses as c', 'r.course_id', '=', 'c.id')
            ->leftJoin('institutions as i', 'r.institution_id', '=', 'i.id')
            ->where('rp.id', '=', $this->exam_registration_period_id)
            ->where('c.id', '=', $courseId)
            ->where('i.id', '=', $institutionId)
            ->select('students.*')
            ->limit(100)
            ->orderBy('surname', 'asc')
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
        $examRegistrationPeriodId = session()->get('exam_registration_period_id');
        $institutionId = session()->get('institution_id');
        $courseId = session()->get('course_id');
        $paperIds = session()->get('paper_ids');
        $studentIds = collect($request->get('students'));
        $numberOfPapers = count($paperIds);
        $numberOfStudents = count($studentIds);
        $yearOfStudy = session()->get('year_of_study');
        $trial = session()->get('trial');

        $costPerPaper = config('settings.fees.paper_registration');

        $totalCost = 0;

        // Get the institution
        $institution = Institution::find($institutionId);

        dd($institution);

        // Get the course
        $course = Course::find($courseId);


        $accountBalance = (float) $institution->account->balance;

        $normalCharge = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
            ->select('surcharge_fees.surcharge_id', 'surcharges.surcharge_name AS surcharge_name', 'surcharge_fees.course_fee')
            ->where('surcharge_fees.course_id', $courseId)
            ->where('surcharges.flag', 1)
            ->first();

        // dd($normalCharge->surcharge_id);

        $bill = 0;

        // Find the registration
        $registration = Registration::where([
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'year_of_study' => $yearOfStudy,
            'registration_period_id' => $examRegistrationPeriodId,
        ])->first();

        if ($registration) {
            // Increment the bill
        } else {
            $registration = new Registration();
            $registration->institution_id = $institution->id;
            $registration->course_id = $courseId;
            $registration->amount = 0;
            $registration->year_of_study = $yearOfStudy;
            $registration->registration_period_id = $examRegistrationPeriodId;
            $registration->surcharge_id = $normalCharge->surcharge_id;
            $registration->save();
        }


        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);

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

            // Register Student Exam Registration
            $existingRegistration = StudentRegistration::where([
                'registration_id' => $registration->id,
                'student_id' => $student->id,
                'trial' => $trial,
            ])->first();

            if ($existingRegistration != null) {
                // Student already has a registration
                // continue;
            } else {
                $courseCodes = Paper::whereIn('id', $paperIds)->pluck('code');

                $existingRegistration = new StudentRegistration();
                $existingRegistration->registration_id = $registration->id;
                $existingRegistration->trial = $trial;
                $existingRegistration->student_id = $student->id;
                $existingRegistration->course_codes = $courseCodes;
                $existingRegistration->no_of_papers = count($paperIds);
                $existingRegistration->sr_flag = 0;
                $existingRegistration->save();
            }

            // Register Student Papers
            $studentCoursePapers = DB::table('course_paper') // Use the actual pivot table name
                ->where('course_id', $course->id)
                ->whereIn('paper_id', $paperIds)
                ->pluck('id');

            // dd($studentCoursePapers);

            foreach ($studentCoursePapers as $coursePaperId) {
                // Create a new StudentPaperRegistration record
                $studentPaperRegistration = new StudentPaperRegistration();
                $studentPaperRegistration->student_registration_id = $existingRegistration->id;
                $studentPaperRegistration->course_paper_id = $coursePaperId;
                $studentPaperRegistration->save();
            }
        }

        if ($bill > $accountBalance) {
            // Alert::error("Account balance to low to complete transaction. Please deposit funds to your account.");

            \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', "Account balance to low to complete exam registration. Please deposit funds to your account and try again.");

            return back();
        }

        $newBalanace = $institution->account->balance - $bill;

        // Increment this amount in registration
        $registration->amount += $newBalanace;
        $registration->save();

        $institution->account->update([
            'balance' => $newBalanace,
        ]);

        $transaction = new Transaction([
            'amount' => $bill,
            'type' => 'debit',
            'account_id' => $institution->account->id,
            'institution_id' => $institution->id,
            'initiated_by' => auth()->user()->id,
            'status' => 'approved',
            'comment' => 'Exam Registration',
        ]);

        $transaction->save();

        Alert::success('Registration successful');
    }
}
