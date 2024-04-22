<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\CoursePaper;
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

    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        session()->put("exam_registration_period_id", $request->get('exam_registration_period_id'));
        session()->put("institution_id", $request->get('institution_id'));
        session()->put("course_id", $request->get('course_id'));
        session()->put("paper_ids", $request->get('paper_ids'));
        session()->put("year_of_study", $request->get('year_of_study'));
        session()->put("trial", $request->get('trial'));
        
        $institutionId = $request->get('institution_id');
        $courseId = $request->get('course_id');

        $query = Student::withoutGlobalScopes();
        $query->select([
            's.id as id',
            's.surname',
            's.firstname',
            's.othername',
            's.gender',
            's.dob',
            's.district_id',
            's.country_id',
            'nsr.nsin as nsin'
        ]);
        $query->from('students As s');
        $query->join('nsin_student_registrations As nsr', 'nsr.student_id', '=', 's.id');
        $query->join('nsin_registrations as nr', 'nr.id', '=', 'nsr.nsin_registration_id');
        $query->join('courses AS c', 'c.id', '=', 'nr.course_id');
        $query->where('nr.institution_id', $institutionId);
        $query->where('nr.course_id', $courseId);
        $query->where('nsr.verify', 1);
        $query->orderBy('nsr.updated_at', 'desc');

        // We need to select students now that have no exam registration
        $query->leftJoin('student_registrations as sr', 's.id', '=', 'sr.student_id');
        $query->whereNull('sr.student_id');

        return [
            'applications' => $query->paginate(),
        ];
    }


    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'New Exam Applications';
    }

    public function description(): ?string
    {
        return 'Select students to register for Exams';
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
        try {
            $examRegistrationPeriodId = session('exam_registration_period_id');
            $institutionId = session('institution_id');
            $courseId = session('course_id');
            $paperIds = session('paper_ids');
            $trial = session('trial');
            $yearOfStudy = session('year_of_study');

            // Get necessary settings
            $settings = \Config::get('settings');
            $costPerPaper = (float) $settings['fees.paper_registration'];

            // Get necessary data
            $institution = Institution::with('account')->find($institutionId);
            $course = Course::find($courseId);
            $studentIds = $request->input('students');

            // Get surcharge for normal registration
            $normalCharge = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
                ->select('surcharge_fees.surcharge_id', 'surcharges.surcharge_name AS surcharge_name', 'surcharge_fees.course_fee')
                ->where('surcharge_fees.course_id', $courseId)
                ->where('surcharges.flag', 1)
                ->firstOrFail();

            // Account Balance
            $accountBalance = (float) $institution->account->balance;

            // Calculate amount to pay
            if ($trial == 'First') {
                $amountToPay = $normalCharge->course_fee * count($studentIds);
            } elseif ($trial == 'Second' || $trial == 'Third') {
                $amountToPay = $costPerPaper * count($paperIds) * count($studentIds);
            }

            // Check account balance
            if ($amountToPay > $accountBalance) {
                throw new \Exception("Insufficient account balance. Please top up your account before proceeding.");
            }

            // Find or create the registration
            $registration = Registration::firstOrNew([
                'institution_id' => $institutionId,
                'course_id' => $courseId,
                'year_of_study' => $yearOfStudy,
                'registration_period_id' => $examRegistrationPeriodId,
            ]);

            if (!$registration->exists) {
                $registration->amount = 0;
                $registration->surcharge_id = $normalCharge->surcharge_id;
                $registration->save();
            }

            // Proceed with registration for each student
            foreach ($studentIds as $studentId) {

                $student = Student::find($studentId);

                // Register Student Exam Registration
                $existingRegistration = StudentRegistration::firstOrNew([
                    'registration_id' => $registration->id,
                    'student_id' => $student->id,
                    'trial' => $trial,
                ]);

                if ($existingRegistration->exists) {
                    $courseCodes = Paper::whereIn('id', $paperIds)->pluck('code');

                    $existingRegistration->course_codes = $courseCodes;
                    $existingRegistration->no_of_papers = count($paperIds);
                    $existingRegistration->sr_flag = 0;
                    $existingRegistration->save();

                    // Create a transaction for this student registration
                    $transaction = new Transaction([
                        'amount' => ($trial == 'First') ? $normalCharge->course_fee : ($costPerPaper * count($paperIds)),
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
                    $institution->account->update(['balance' => $newBalance]);

                    // Register Student Papers
                    $studentCoursePapers = CoursePaper::where('course_id', $course->id)
                        ->whereIn('paper_id', $paperIds)
                        ->pluck('id');

                    $studentPaperRegistrations = [];
                    foreach ($studentCoursePapers as $coursePaperId) {
                        $studentPaperRegistrations[] = [
                            'student_registration_id' => $existingRegistration->id,
                            'course_paper_id' => $coursePaperId,
                        ];
                    }

                    StudentPaperRegistration::insert($studentPaperRegistrations);
                }
            }

            $numberOfStudents = count($studentIds);
            $examTotal = $amountToPay;
            $totalDeduction = $examTotal;
            $remainingBalance = $institution->account->balance;

            $amountForExam = 'Ush ' . number_format($examTotal);
            $totalDeductionFormatted = 'Ush ' . number_format($totalDeduction);
            $remainingBalanceFormatted = 'Ush ' . number_format($remainingBalance);


            \RealRashid\SweetAlert\Facades\Alert::success('Action Completed', "<table class='table table-condensed table-striped table-hover' style='text-align: left; font-size:12px;'><tbody><tr><th style='text-align: left; font-size:12px;'>Students registered</th><td>$numberOfStudents</td></tr><tr><th style='text-align: left; font-size:12px;'>Exam Registration</th><td>$examTotal</td></tr><tr><th style='text-align: left; font-size:12px;'>Total Deduction</th><td>$totalDeductionFormatted</td></tr><tr><th style='text-align: left; font-size:12px;'>Remaining Balance</th><td>$remainingBalanceFormatted</td></tr></tbody></table>")->persistent(true)->toHtml();

        } catch (\Throwable $th) {
            \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', $th->getMessage())->persistent(true);
        }
    }

}
