<?php

namespace App\Orchid\Screens;

use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Models\Transaction;
use App\Orchid\Layouts\RegisterStudentsForNSINForm;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Alert;
use Orchid\Support\Facades\Layout;

class NewNsinApplicationsScreen extends Screen
{
    public $institutionId;
    public $courseId;
    public $nsinRegistrationPeriodId;

    public function __construct(Request $request)
    {
        $this->institutionId = request()->get('institution_id');
        $this->courseId = request()->get('course_id');
        $this->nsinRegistrationPeriodId = request()->get('nsin_registration_period_id');

        $institutionId = $request->input('institution_id');
        $courseId = $request->input('course_id');
        $nsinRegistrationPeriodId = $request->input('nsin_registration_period_id');

        // Save to session
        session()->put('institution_id', $institutionId);
        session()->put('course_id', $courseId);
        session()->put('nsin_registration_period_id', $nsinRegistrationPeriodId);
    }


    /**
     * Fetch data to be displayed on the screen.
     *
     * @return array
     */
    public function query(): iterable
    {
        $query = Student::leftJoin('nsin_student_registrations as nsr', 'students.id', '=', 'nsr.student_id')
            ->leftJoin('nsin_registrations as nr', 'nsr.nsin_registration_id', '=', 'nr.id')
            ->leftJoin('nsin_registration_periods as nsp', function ($join) {
                $join->on('nr.year_id', '=', 'nsp.year_id')
                    ->on('nr.month', '=', 'nsp.month');
            })
            ->whereNull('nsp.id')
            ->orWhere('nsp.id', '<>', $this->nsinRegistrationPeriodId)
            ->select('students.*')
            ->limit(100)
            ->orderBy('surname', 'asc')
            ->get();

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
        return 'Apply Students For NSINs';
    }

    public function description(): ?string
    {
        return 'Select students for NSIN Application from the table below and submit';
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
            RegisterStudentsForNSINForm::class
        ];
    }

    public function submitNSINs(Request $request)
    {
        $settings = \Config::get('settings');
        $nsinRegistrationFee = $settings['fees.nsin_registration'] ?? 0;
        $logbookFee = $settings['logbook_fees'] ?? 0;

        // Log the start of the function
        \Log::info('NSIN registration submission started.');

        $students = collect($request->get('students'))->keys();

        if ($students->count() == 0) {
            Alert::error('Unable to submit data. You have not selected any students to register');
            return;
        }

        $nrpID = session('nsin_registration_period_id');
        $institutionId = session('institution_id');
        $courseId = session('course_id');
        $studentIds = collect($request->get('students'))->keys();

        // Log session and input data
        \Log::info('Session data:', [
            'nsin_registration_period_id' => $nrpID,
            'institution_id' => $institutionId,
            'course_id' => $courseId,
            'student_ids' => $studentIds,
        ]);

        $nsinRegistrationPeriod = NsinRegistrationPeriod::find($nrpID);

        $yearId = $nsinRegistrationPeriod->year_id;
        $month = $nsinRegistrationPeriod->month;

        $fees = 0;

        foreach ($studentIds as $studentId) {
            $fees += $nsinRegistrationFee + $logbookFee;
        }

        // Log total fee calculation
        \Log::info('Total fees calculated:', ['total_fees' => $fees]);

        $institution = Institution::find($institutionId);

        // Log institution data
        \Log::info('Institution data:', ['institution' => $institution]);

        if ($fees > $institution->account->balance) {
            Alert::error('Account balance too low to complete this transaction. Please top up to continue');
            return;
        }

        // Log balance check
        \Log::info('Account balance check passed.');

        // Find or create the NSIN registration
        $nsinRegistration = NsinRegistration::firstOrCreate([
            'year_id' => $yearId,
            'month' => $month,
            'institution_id' => $institutionId,
            'course_id' => $courseId,
        ]);

        foreach ($studentIds as $studentId) {
            // Check if the student is already registered for the same period, institution, and course
            $existingRegistration = NsinStudentRegistration::where([
                'nsin_registration_id' => $nsinRegistration->id,
                'student_id' => $studentId,
                'verify' => 0
            ])->first();

            if (!$existingRegistration) {
                $nsinStudentRegistration = new NsinStudentRegistration();
                $nsinStudentRegistration->nsin_registration_id = $nsinRegistration->id;
                $nsinStudentRegistration->student_id = $studentId;
                $nsinStudentRegistration->verify = 0;
                $nsinStudentRegistration->save();

                // Create Transaction for NSIN registration fee for this student
                $nsinTransaction = new Transaction([
                    'amount' => $nsinRegistrationFee,
                    'type' => 'debit',
                    'account_id' => $institution->account->id,
                    'institution_id' => $institution->id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'NSIN Registration Fee for Student ID: ' . $studentId,
                ]);

                $nsinTransaction->save();

                // Create Transaction for logbook fee for this student
                $logbookTransaction = new Transaction([
                    'amount' => $logbookFee,
                    'type' => 'debit',
                    'account_id' => $institution->account->id,
                    'institution_id' => $institution->id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'Logbook Fee for Student ID: ' . $studentId,
                ]);

                $logbookTransaction->save();
            }
        }

        // Update institution account balance
        $newBalance = $institution->account->balance - $fees;

        // Log new balance calculation
        \Log::info('New balance calculated:', ['new_balance' => $newBalance]);

        $institution->account->update([
            'balance' => $newBalance,
        ]);

        Alert::success('Registration successful');

        // Log success message
        \Log::info('NSIN registration successful.');

        // Redirect
        redirect(route('platform.registration.nsin.applications.list'));
    }


}
