<?php

namespace App\Orchid\Screens;

use App\Models\Course;
use App\Models\Institution;
use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentPaperRegistration;
use App\Models\StudentRegistration;
use App\Models\SurchargeFee;
use App\Models\Transaction;
use App\Models\TransactionLog;
use App\Orchid\Layouts\ExamRegistrationTable;
use DB;
use Exception;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Screen;
use RealRashid\SweetAlert\Facades\Alert;

class ExamRegistrationDetailScreen extends Screen
{
    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(Request $request): iterable
    {
        $registration_period_id = $request->get('registration_period_id');
        $registration_id = $request->get('registration_id');
        $institution_id = $request->get('institution_id');
        $course_id = $request->get('course_id');

        session()->put('registration_id', $registration_id);
        session()->put('institution_id', $institution_id);
        session()->put('course_id', $course_id);

        $query = RegistrationPeriod::select(
            's.id as id',
            's.surname',
            's.firstname',
            's.othername',
            's.gender',
            's.dob',
            's.district_id',
            's.country_id',
            's.nsin as nsin',
            's.telephone',
            's.passport',
            's.passport_number',
            's.lin',
            's.email',
            'sr.trial',
            'sr.course_codes',
            'sr.no_of_papers',
            'sr.created_at',
            'sr.updated_at'
        )
            ->from('students as s')
            ->join('student_registrations as sr', 'sr.student_id', '=', 's.id')
            ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
            ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->where('rp.id', $registration_period_id)
            ->where('r.id', session('registration_id'));

        if (auth()->user()->inRole('institution')) {
            $query->where('r.institution_id', auth()->user()->institution_id);
        }

        return [
            'students' => $query->paginate()
        ];
    }

    /**
     * The name of the screen displayed in the header.
     *
     * @return string|null
     */
    public function name(): ?string
    {
        return 'Exam Registrations';
    }

    /**
     * The screen's action buttons.
     *
     * @return \Orchid\Screen\Action[]
     */
    public function commandBar(): array
    {
        return [
            DropDown::make('Select Action')
                ->class('btn btn-primary btn-md')
                ->list([
                    Button::make('Rollback Exam Registration')
                        ->icon('bs.receipt')
                        ->class('btn link-success')
                        ->canSee(auth()->user()->inRole('administrator'))
                        ->method('rollback'),

                    Button::make('Delete Registrations')
                        ->icon('bs.trash3')
                        ->confirm(__('Once you confirm, Selected exam registrations will be deleted for the current period'))
                        ->method('delete')
                        ->class('btn link-danger'),

                ])
        ];
    }

    /**
     * The screen's layout elements.
     *
     * @return \Orchid\Screen\Layout[]|string[]
     */
    public function layout(): iterable
    {
        $table = (new ExamRegistrationTable);
        return [
            $table
        ];
    }

    public function rollback(Request $request)
    {
        DB::beginTransaction();

        try {

            $registration_id = session()->get('registration_id');

            // Get Student IDs from form
            $studentIds = collect($request->get('students'))->values();

            $examRegistration = Registration::findOrFail($registration_id);

            // Retrieve necessary data from session
            $examRegistrationPeriodId = session('exam_registration_period_id');
            $institutionId = $examRegistration->institution_id;
            $courseId = $examRegistration->institution_id;
            // $paperIds = session('paper_ids');
            // $trial = session('trial');
            // $yearOfStudy = session('year_of_study');

            // Fetch settings
            $settings = config('settings');

            // Calculate cost per paper
            $costPerPaper = (float) $settings['fees.paper_registration'];

            // Retrieve institution and course
            $institution = Institution::with('account')->findOrFail($institutionId);
            $account = $institution->account;
            $course = Course::findOrFail($courseId);
            $studentIds = $request->input('students');

            if (empty($studentIds) || count($studentIds) == 0) {
                throw new Exception('You have not selected any students. Please select students that you wish to apply for Exams');
            }

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
                        $studentRegistration = StudentRegistration::where('student_id', $studentId)
                            ->where('registration_id', $examRegistration->id);

                        $papers = $studentRegistration->no_of_papers;

                        $transactionAmount = $costPerPaper * $papers;
                    }

                    // Add transaction amount to total
                    $totalTransactionAmount += $transactionAmount;
                }
            }

            if (!$examRegistration) {
                throw new Exception('No registration found for this course.');
            }

            $studentRegistrations = StudentRegistration::where('registration_id', $examRegistration->id)
                ->whereIn('student_id', $studentIds)
                ->get();

            foreach ($studentRegistrations as $studentRegistration) {
                $studentPaperRegistrations = StudentPaperRegistration::where('student_registration_id', $studentRegistration->id)
                    ->delete();

                $studentRegistration->delete();
            }

            $examTransaction = Transaction::create([
                'amount' => $totalTransactionAmount,
                'type' => 'credit',
                'account_id' => $institution->account->id,
                'institution_id' => $institution->id,
                'initiated_by' => auth()->user()->id,
                'status' => 'approved',
                'description' => 'REVERSAL FOR EXAM REGISTRATION FOR ' . count($studentIds) . ' STUDENTS'
            ]);

            $examTransactionLog = TransactionLog::create([
                'transaction_id' => $examTransaction->id,
                'user_id' => auth()->user()->id,
                'action' => 'created',
                'description' => 'REVERSAL FOR EXAM REGISTRATION FOR ' . count($studentIds) . ' STUDENTS'
            ]);

            // Update account balance
            $remainingBalance = $institution->account->balance + $examRegistration->amount;
            $institution->account->update([
                'balance' => $remainingBalance,
            ]);

            DB::commit();

            Alert::success('Action Completed', 'Students unregistered successfully.');

        } catch (\Throwable $th) {
            DB::rollBack();

            Alert::error('Action Failed', 'Unable to unregister students. Failed with error ' . $th->getMessage());
        }
    }

    public function delete(Request $request)
    {
    }
}
