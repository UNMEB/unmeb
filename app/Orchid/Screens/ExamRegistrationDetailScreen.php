<?php

namespace App\Orchid\Screens;

use App\Models\Account;
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
        try {
            DB::beginTransaction();

            $registration_id = session()->get('registration_id');
            $institution_id = session()->get('institution_id');
            $course_id = session()->get('course_id');

            // Fetch settings
            $settings = config('settings');
            $costPerPaper = (float) $settings['fees.paper_registration'];

            // Get Student IDs from form
            $studentIds = $request->get('students');

            $registration = Registration::findOrFail($registration_id);

            $normalCharge = SurchargeFee::join('surcharges', 'surcharge_fees.surcharge_id', '=', 'surcharges.id')
                ->select('surcharge_fees.surcharge_id', 'surcharges.surcharge_name AS surcharge_name', 'surcharge_fees.course_fee')
                ->where('surcharge_fees.course_id', $course_id)
                ->where('surcharges.flag', 1)
                ->firstOrFail();

            $institution = Institution::findOrFail($institution_id);
            $course = Course::findOrFail($course_id);
            $account = Account::where('institution_id', $institution->id)->first();

            if (empty($studentIds) || count($studentIds) == 0) {
                throw new Exception('You have not selected any students. Please select students that you wish to apply for Exams');
            }

            $totalTransactionAmount = 0;

            foreach ($studentIds as $studentId) {
                $studentRegistration = StudentRegistration::where('student_id', $studentId)
                    ->where('registration_id', $registration->id)
                    ->first();

                $trial = $studentRegistration->trial;

                $transactionAmount = 0;

                if ($trial == 'First') {
                    $transactionAmount = $normalCharge->course_fee;
                } else if ($trial == 'Second' || $trial == 'Third') {
                    $papers = $studentRegistration->no_of_papers;
                    $transactionAmount = $costPerPaper * $papers;
                }

                $totalTransactionAmount += $transactionAmount;

                StudentPaperRegistration::where('student_registration_id', $studentRegistration->id)
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
                'comment' => 'REVERSAL FOR EXAM REGISTRATION FOR ' . count($studentIds) . ' STUDENTS'
            ]);

            $examTransactionLog = TransactionLog::create([
                'transaction_id' => $examTransaction->id,
                'user_id' => auth()->user()->id,
                'action' => 'created',
                'description' => 'REVERSAL FOR EXAM REGISTRATION FOR ' . count($studentIds) . ' STUDENTS'
            ]);


            // Update account balance
            $remainingBalance = $institution->account->balance + $totalTransactionAmount;
            $account->balance = $remainingBalance;
            $account->save();

            DB::commit();

            Alert::success('Action Completed', 'Students registrations successfully rolled back.');


        } catch (Exception $ex) {
            DB::rollBack();

            throw $ex;
        }
    }

    public function delete(Request $request)
    {
    }
}
