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
            's.id AS student_id',
            's.surname',
            's.firstname',
            's.othername',
            's.nsin'
        ])->from('students AS s')
            ->join('nsin_student_registrations AS nsr', 'nsr.student_id', '=', 's.id')
            ->join('nsin_registrations AS nr', 'nsr.nsin_registration_id', '=', 'nr.id')
            ->where('nr.institution_id', '=', session('institution_id'))
            ->where('nr.course_id', '=', session('course_id'))
            ->whereNotIn('s.id', function($query) {
                $query->select('student_id')
                    ->distinct()
                    ->from('student_registrations as sr')
                    ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
                    ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
                    ->where('rp.flag', '=', 1);
            })
            ->orderBy('s.nsin', 'ASC');


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

            $settings = \Config::get('settings');

            $costPerPaper = (float) $settings['fees.paper_registration'];

            $institution = Institution::with('account')->findOrFail($institutionId);
            $course = Course::findOrFail($courseId);
            $studentIds = $request->input('students');

            // Get surcharge for normal registration
            $normalCharge = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
                ->select('surcharge_fees.surcharge_id', 'surcharges.surcharge_name AS surcharge_name', 'surcharge_fees.course_fee')
                ->where('surcharge_fees.course_id', $courseId)
                ->where('surcharges.flag', 1)
                ->firstOrFail();

            $accountBalance = (float) $institution->account->balance;

            // Calculate amount to pay
            if ($trial == 'First') {
                $amountToPay = $normalCharge->course_fee;
            } elseif ($trial == 'Second' || $trial == 'Third') {
                $amountToPay = $costPerPaper * count($paperIds);
            }

            if ((count($studentIds) * $amountToPay ) > $accountBalance) {
                throw new \Exception("Insufficient account balance. Please top up your account before proceeding.");
            }

            $registration = Registration::where('institution_id', $institution->id)
                            ->where('course_id', $course->id)
                            ->first();
            if ($registration == null) {
                $registration = new Registration();
                $registration->institution_id = $institution->id;
                $registration->course_id = $course->id;
                $registration->year_of_study = $yearOfStudy;
                $registration->registration_period_id = $examRegistrationPeriodId;
                $registration->date_time = now();
                $registration->amount = 0;
                $registration->surcharge_id = $normalCharge->surcharge_id;
                $registration->save();
            }


            foreach ($studentIds as $studentId) {
                $student = Student::find($studentId);

                $studentRegistration = StudentRegistration::firstOrNew([
                    'registration_id' => $registration->id,
                    'student_id' => $student->id,
                    'trial' => $trial,
                ]);

                $courseCodes = Paper::whereIn('id', $paperIds)->pluck('code');

                $studentRegistration->course_codes = $courseCodes;
                $studentRegistration->no_of_papers = count($paperIds);
                $studentRegistration->sr_flag = 0;
                $studentRegistration->save();

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
                $newBalance = $institution->account->balance - (($trial == 'First') ? $normalCharge->course_fee : ($costPerPaper * count($paperIds)));
                $institution->account->update(['balance' => $newBalance]);

                $studentCoursePapers = CoursePaper::where('course_id', $course->id)
                        ->whereIn('paper_id', $paperIds)
                        ->pluck('id');

                $studentPaperRegistrations = [];
                foreach ($studentCoursePapers as $coursePaperId) {
                    $studentPaperRegistrations[] = [
                        'student_registration_id' => $studentRegistration->id,
                        'course_paper_id' => $coursePaperId,
                    ];
                }

                StudentPaperRegistration::insert($studentPaperRegistrations);
            }

            $numberOfStudents = count($studentIds);
            $examTotal = $amountToPay * $numberOfStudents;
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
