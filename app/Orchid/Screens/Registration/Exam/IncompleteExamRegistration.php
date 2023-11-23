<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\Account;
use App\Models\Institution;
use App\Models\Paper;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\SurchargeFee;
use App\Models\Transaction;
use App\Orchid\Layouts\RegisterStudentsForExamForm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\Link;
use Orchid\Screen\Actions\ModalToggle;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class IncompleteExamRegistration extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {

        $page = request('page', 1);

        $cacheKey = 'registration_query_results' . $page;

        // Attempt to retrieve the query results from the cache
        $cachedResults = Cache::get($cacheKey);

        if ($cachedResults !== null) {
            // If cached results exist, return them without modifying pagination
            return ['registrations' => $cachedResults];
        }

        $query = Registration::filters()
            ->from('registrations AS r')
            ->select('r.id as registration_id', 'i.id AS institution_id', 'i.institution_name', 'c.course_name', 'rp.id as registration_period_id', 'rp.reg_start_date', 'rp.reg_end_date', 'r.completed', 'r.verify', 'r.approved')
            ->selectRaw('COUNT(sr.id) as registered_students')
            ->selectRaw('SUM(CASE WHEN sr.sr_flag = 0 THEN 1 ELSE 0 END) as to_register')
            ->join('institutions as i', 'r.institution_id', '=', 'i.id')
            ->join('courses as c', 'r.course_id', '=', 'c.id')
            ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->leftJoin('student_registrations as sr', 'sr.registration_id', '=', 'r.id')
            ->groupBy('r.id', 'i.institution_name', 'c.course_name', 'rp.id', 'rp.reg_start_date', 'rp.reg_end_date', 'r.completed', 'r.verify', 'r.approved')
            ->orderBy('to_register', 'desc');

        $perPage = request('per_page', 15); // Adjust the default per page count if needed

        $results = ['registrations' => $query->paginate($perPage, ['*'], 'page', $page)];

        // Set the cache duration to 7 days (10080 minutes)
        $minutes = 10080;

        // Store the query results in the cache for future use
        Cache::put($cacheKey, $results['registrations'], $minutes); // Replace $minutes with your desired cache duration

        return $results;
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Incomplete Exam Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): iterable
    {
        return [
            ModalToggle::make('Register Students For Exams')
                ->modalTitle('Register Student For Exams')
                ->modal('examRegistrationModal')
                ->method('register')

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

            Layout::modal('examRegistrationModal', RegisterStudentsForExamForm::class),

            Layout::table('registrations', [
                TD::make('institution_name', 'Institution'),
                TD::make('course_name', 'Program'),
                TD::make('year_of_study', 'Year Of Study'),
                TD::make('reg_start_date', 'Registration Start Date'),
                TD::make('reg_end_date', 'Registration Start Date'),
                TD::make('registered_students', 'Students Registered'),
                TD::make('to_register', 'Students to Registered'),
                TD::make('actions', 'Actions')->render(fn (Registration $data) => Link::make('Details')
                    ->class('btn btn-primary btn-sm link-primary')
                    ->disabled($data->to_register == 0)
                    ->route('platform.registration.exam.incomplete.details', [
                        'institution_id' => $data->institution_id,
                        'course_id' => $data->course_id,
                        'registration_id' => $data->id
                    ]))


            ])
        ];
    }

    public function register(Request $request)
    {
        $examRegistrationPeriodId = $request->input('exam_registration_period_id');
        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $paperIds = $request->input('paper_ids');
        $studentIds = $request->input('student_ids');
        $numberOfPapers = count($paperIds);
        $numberOfStudents = count($request->input('student_ids'));
        $yearOfStudy = $request->input('year_of_study');
        $trial = $request->input('trial');

        $costPerPaper = 50000;

        $totalCost = 0;

        // Get the institution
        $institution = Institution::find($institutionId);


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

            // Check if the student is already registered for the same period, instituion and course
            $existingRegistration = StudentRegistration::where([
                'registration_id' => $registration->id,
                'trial' => $trial,
                'student_id' => $student->id,
            ])->first();

            if (!$existingRegistration) {

                $courseCodes = Paper::whereIn('id', $paperIds)->pluck('code');

                $examStudentRegistration = new StudentRegistration();
                $examStudentRegistration->registration_id = $registration->id;
                $examStudentRegistration->trial = $trial;
                $examStudentRegistration->student_id = $student->id;
                $examStudentRegistration->course_codes = $courseCodes;
                $examStudentRegistration->no_of_papers = count($paperIds);
                $examStudentRegistration->sr_flag = 0;
                $examStudentRegistration->save();
            }
        }

        if ($bill > $accountBalance) {
            Alert::error("Account balance to low to complete transaction. Please deposit funds to your account.");
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
            'comment'  => 'Exam Registration',
        ]);

        $transaction->save();

        Alert::success('Registration successful');
    }
}
