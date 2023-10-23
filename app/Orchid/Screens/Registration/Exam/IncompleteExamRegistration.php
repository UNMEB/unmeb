<?php

namespace App\Orchid\Screens\Registration\Exam;

use App\Models\Account;
use App\Models\Institution;
use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\SurchargeFee;
use App\Models\Transaction;
use App\Orchid\Layouts\RegisterStudentsForExamForm;
use Illuminate\Http\Request;
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
        $query = Registration::query()
            ->select([
                'r.id',
                'i.id as institution_id',
                'i.institution_name',
                'c.id as course_id',
                'c.course_name',
                'r.year_of_study',
                'rp.reg_start_date',
                'rp.reg_end_date',
                DB::raw("(SELECT COUNT(*) FROM student_registrations WHERE registration_id = r.id) as registered_students")
            ])
            ->from('registrations as r')
            ->join('institutions as i', 'r.institution_id', '=', 'i.id')
            ->join('courses as c', 'r.course_id', '=', 'c.id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->where('r.completed', 0);

        return [
            'registrations' => $query->paginate()
        ];
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
                TD::make('actions', 'Actions')->render(fn (Registration $data) => Link::make('Details')
                ->class('btn btn-primary btn-sm link-primary')
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
        ->select('surcharge_fees.surcharge_id', 'surcharges.name AS surcharge_name', 'surcharge_fees.course_fee')
        ->where('surcharge_fees.course_id', $courseId)
            ->where('surcharges.is_active', 1)
            ->first()
            ->course_fee;

        $bill = 0;

        foreach ($studentIds as $studentId) {
            $student = Student::find($studentId);

            // if first attempt register normally
            if ($trial == 'First') {
                $bill += $normalCharge;
            } else if ($trial == 'Second') {
                $costToPay = ($costPerPaper + ($costPerPaper * 0.5)) * count($paperIds);
                $bill += $costToPay;
            } else {
                $costToPay = ($costPerPaper + ($costPerPaper * 1)) * count($paperIds);
                $bill += $costToPay;
            }


        }

        if ($bill > $accountBalance) {
            Alert::error("Account balance to low to complete transaction. Please deposit funds to your account.");
            return back();
        }

        $newBalanace = $institution->account->balance - $bill;
        $institution->account->update([
            'balance' => $newBalanace,
        ]);

        $transaction = new Transaction([
            'amount' => $bill,
            'type' => 'debit',
            'account_id' => $institution->account->id,
            'institution_id' => $institution->id,
            'initiated_by' => auth()->user()->id,
        ]);

        $transaction->save();

        Alert::success("Students registered");
    }
}
