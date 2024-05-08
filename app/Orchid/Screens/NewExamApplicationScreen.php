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
use App\Models\TransactionLog;
use App\Models\TransactionMeta;
use App\Orchid\Layouts\RegisterStudentsForExamsTable;
use DB;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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

        $registeredStudentIds = Student::withoutGlobalScopes()
            ->from('students as s')
            ->join('student_registrations as sr', 'sr.student_id', '=', 's.id')
            ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
            ->join('institutions as i', 'i.id', '=', 'r.institution_id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->where('sr.trial', '=', session('trial'))
            ->where('r.year_of_study', session('year_of_study'))
            ->where('r.institution_id', session('institution_id'))
            ->where('rp.flag', '=', 1)
            ->pluck('s.id')
            ->toArray();

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
                's.nsin',
                's.passport_number',
                's.nin',
                's.telephone',
                's.refugee_number',
                's.lin',
                's.date_time',
                's.passport',
            ])
            ->from('students As s')
            ->whereNotNull('s.nsin')
            ->whereNotIn('s.id', $registeredStudentIds)
            ->whereNotIn('s.id', session('selected_student_ids', []))
            ->orderBy('s.nsin', 'asc');

        // Get current course code
        $course = Course::find(session('course_id'));
        if ($course) {
            $course_code = $course->course_code;
            $query->whereRaw("SUBSTRING_INDEX(SUBSTRING_INDEX(s.nsin, '/', -2), '/', -1) = '$course_code'");
        }

        if (auth()->user()->inRole('institution')) {
            $query->where('s.institution_id', auth()->user()->institution_id);
        }

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
        DB::beginTransaction();

        try {
            // Retrieve necessary data from session
            $examRegistrationPeriodId = session('exam_registration_period_id');
            $institutionId = session('institution_id');
            $courseId = session('course_id');
            $paperIds = session('paper_ids');
            $trial = session('trial');
            $yearOfStudy = session('year_of_study');

            // Fetch settings
            $settings = config('settings');

            // Calculate cost per paper
            $costPerPaper = (float) $settings['fees.paper_registration'];

            // Retrieve institution and course
            $institution = Institution::with('account')->findOrFail($institutionId);
            $account = $institution->account;
            $accountBalance = $account->balance;
            $course = Course::findOrFail($courseId);
            $studentIds = $request->input('students');

            $courseCodes = Paper::whereIn('id', $paperIds)->pluck('code');

            if (empty($studentIds) || count($studentIds) == 0) {
                throw new Exception('You have not selected any students. Please select students that you wish to apply for Exams');
            }

            // Append new student IDs to the session array if it exists
            $existingStudentIds = session('selected_student_ids', []);
            $newStudentIds = array_merge(
                $existingStudentIds,
                $studentIds
            );
            session(['selected_student_ids' => $newStudentIds]);

            // Get surcharge for normal registration
            $normalCharge = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
                ->select('surcharge_fees.surcharge_id', 'surcharges.surcharge_name AS surcharge_name', 'surcharge_fees.course_fee')
                ->where('surcharge_fees.course_id', $course->id)
                ->where('surcharges.flag', 1)
                ->firstOrFail();

            $totalTransactionAmount = 0;

            foreach ($studentIds as $studentId) {
                $student = Student::withoutGlobalScopes()->whereId($studentId)->first();

                if ($student) {
                    // Calculate transaction amount based on trial type
                    $transactionAmount = 0;
                    if ($trial == 'First') {
                        // These students will be charged normal charge each
                        $transactionAmount = $normalCharge->course_fee;
                    } else if ($trial == 'Second' || $trial == 'Third') {
                        // These students will be charged basing on cost per paper * number of papers
                        $transactionAmount = $costPerPaper * count($paperIds);
                    }

                    // Add transaction amount to total
                    $totalTransactionAmount += $transactionAmount;
                }
            }

            // Check if account balance is sufficient
            if ($accountBalance < $totalTransactionAmount) {
                throw new Exception("Insufficient account balance. Please top up your account before proceeding.");
            }

            $registration = Registration::where(
                [
                    'institution_id' => $institution->id,
                    'course_id' => $course->id,
                    'registration_period_id' => (int) $examRegistrationPeriodId,
                    'year_of_study' => $yearOfStudy,
                ]
            )->first();

            if (!$registration) {
                $registration = new Registration();
                $registration->institution_id = $institution->id;
                $registration->course_id = $course->id;
                $registration->year_of_study = $yearOfStudy;
                $registration->amount = $totalTransactionAmount;
                $registration->registration_period_id = (int) $examRegistrationPeriodId;
                $registration->completed = 0;
                $registration->verify = 0;
                $registration->approved = 0;
                $registration->surcharge_id = $normalCharge->surcharge_id;
                $registration->date_time = now();
                $registration->save();
            }

            foreach ($studentIds as $studentId) {
                $student = Student::withoutGlobalScopes()->whereId($studentId)->first();

                if ($student) {

                    $studentRegistration = StudentRegistration::firstOrCreate([
                        'student_id' => $student->id,
                        'registration_id' => $registration->id,
                        'trial' => $trial,
                    ], [
                        'course_codes' => $courseCodes,
                        'no_of_papers' => count($paperIds),
                        'sr_flag' => 0,
                        'remarks' => 'Registration Pending'
                    ]);

                    // Retrieve course paper IDs for the student
                    $studentCoursePapers = CoursePaper::where('course_id', $course->id)
                        ->whereIn('paper_id', $paperIds)
                        ->pluck('id');

                    // Insert student paper registrations
                    $studentPaperRegistrations = [];
                    foreach ($studentCoursePapers as $coursePaperId) {
                        $studentPaperRegistrations[] = [
                            'student_registration_id' => $studentRegistration->id,
                            'course_paper_id' => $coursePaperId,
                        ];
                    }
                    StudentPaperRegistration::insert($studentPaperRegistrations);

                }
            }

            // Create a transaction
            $examTransaction = Transaction::create([
                'amount' => $totalTransactionAmount,
                'type' => 'debit',
                'account_id' => $institution->account->id,
                'institution_id' => $institution->id,
                'initiated_by' => auth()->user()->id,
                'status' => 'approved',
                'description' => 'EXAM REGISTRATION FOR ' . count($studentIds) . ' STUDENTS'
            ]);

            // Create transaction log for EXAM registration
            $examTransactionLog = TransactionLog::create([
                'transaction_id' => $examTransaction->id,
                'user_id' => auth()->user()->id,
                'action' => 'created',
                'description' => 'EXAM REGISTRATION FOR ' . count($studentIds) . ' STUDENTS'
            ]);

            // Get browser and location information
            $userAgent = $request->header('User-Agent');
            $ipAddress = $request->ip();
            $browser = $this->parseUserAgent($userAgent);
            $networkMeta = $this->getNetworkMeta($ipAddress);

            // Create transaction meta for EXAM registration
            $examTransactionMeta = TransactionMeta::create([
                'transaction_id' => $examTransaction->id,
                'key' => 'exam_registration_info',
                'value' => [
                    'exam_registration_id' => $registration->id,
                    'students' => $studentIds,
                ]
            ]);

            $remainingBalance = $institution->account->balance - $totalTransactionAmount;

            $institution->account->update([
                'balance' => $institution->account->balance - $totalTransactionAmount,
            ]);

            $amountForExam = 'Ush ' . number_format($totalTransactionAmount);
            $remainingBalanceFormatted = 'Ush ' . number_format($remainingBalance);
            $numberOfStudents = count($studentIds);

            DB::commit();

            \RealRashid\SweetAlert\Facades\Alert::success('Action Completed', "<table class='table table-condensed table-striped table-hover' style='text-align: left; font-size:12px;'><tbody><tr><th style='text-align: left; font-size:12px;'>Students registered</th><td>$numberOfStudents</td></tr><tr><th style='text-align: left; font-size:12px;'>Exam Registration</th><td>$amountForExam</td></tr><tr><th style='text-align: left; font-size:12px;'>Total Deduction</th><td>$amountForExam</td></tr><tr><th style='text-align: left; font-size:12px;'>Remaining Balance</th><td>$remainingBalanceFormatted</td></tr></tbody></table>")->persistent(true)->toHtml();

        } catch (\Throwable $th) {
            throw $th;

            // \RealRashid\SweetAlert\Facades\Alert::error('Action Failed', 'Unable to complete Exam registration for selected students. Failed with error ' . $th->getMessage());
        }
    }

    private function parseUserAgent($userAgent)
    {
        $agent = new \Jenssegers\Agent\Agent();
        $agent->setUserAgent($userAgent);
        return $agent->browser() . ' on ' . $agent->platform();
    }

    private function getNetworkMeta($ip)
    {
        // Check if testing offline
        if ($ip === '127.0.0.1') {
            return [];
        }

        $response = Http::get('http://ip-api.com/json/' . $ip);

        if ($response->successful()) {
            $data = $response->json();

            return [
                'country' => $data['country'],
                'country_code' => $data['countryCode'],
                'region' => $data['regionName'],
                'city' => $data['city'],
                'latitude' => $data['lat'],
                'longitude' => $data['lon'],
                'timezone' => $data['timezone'],
                'isp' => $data['isp'],
                'organization' => $data['org'],
                'as' => $data['as']
            ];
        }

        return [];
    }

}
