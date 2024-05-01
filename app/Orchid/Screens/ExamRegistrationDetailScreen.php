<?php

namespace App\Orchid\Screens;

use App\Models\Registration;
use App\Models\RegistrationPeriod;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\Transaction;
use App\Orchid\Layouts\ExamRegistrationTable;
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
            'sr.no_of_papers'
        )
            ->from('students as s')
            ->join('student_registrations as sr', 'sr.student_id', '=', 's.id')
            ->join('registrations as r', 'sr.registration_id', '=', 'r.id')
            ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->where('rp.flag', 1)
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
            $validatedData = $request->validate([
                'students' => 'required|array',
                'students.*' => 'integer|exists:students,id', // Assuming students are stored in a table named 'students' with 'id' column.
            ]);

            $studentIds = $validatedData['students'];

            $registration_id = session()->get('registration_id');
            $institution_id = session()->get('institution_id');
            $course_id = session()->get('course_id');

            foreach ($studentIds as $key => $studentId) {

                // Get the student
                $student = Student::findOrFail($studentId);

                // Find the NSIN transactions and reverse it
                $examTransaction = Transaction::where('comment', '=', 'Exam Registration Fee for Student ID: ' . $studentId)->first();

                // Find the registration table and rollback amount
                $registration = Registration::where([
                    'institution_id' => $institution_id,
                    'course_id' => $course_id,
                    'id' => $registration_id
                ])->first();

                // Revert the balance less this transaction for exam
                if ($registration) {
                    $registration->amount -= $examTransaction->amount;
                    $registration->save();
                }

                // Find the student registration and remove it
                $studentRegistration = StudentRegistration::where([
                    'student_id' => $student->id,
                    'registration_id' => $registration_id,
                ])->first();

                if (!$studentRegistration) {
                    // Delete the student registration
                    $studentRegistration->delete();
                }

                if ($examTransaction) {
                    // Retrieve the account associated with the transaction
                    $account = $examTransaction->account;

                    // Create a new transaction to record the reversal
                    $reversalTransaction = new Transaction([
                        'amount' => $examTransaction->amount, // reverse the amount
                        'type' => 'credit', // credit the reversed amount
                        'account_id' => $examTransaction->account_id,
                        'institution_id' => $examTransaction->institution_id,
                        'initiated_by' => auth()->user()->id,
                        'status' => 'approved',
                        'comment' => 'Reversal of Exam Registration Fee for Student ID: ' . $studentId,
                    ]);
                    $reversalTransaction->save();

                    // Update the original transaction to mark it as reversed
                    $examTransaction->status = 'reversed';
                    $examTransaction->save();

                    // Update the account balance
                    $account->balance += $examTransaction->amount;
                    $account->save();
                }
            }

            Alert::success('Action Complete', count($studentIds) . ' Exam registration successfully recalled and transactions reversed.')->persistent(true);

        } catch (\Throwable $th) {
            Alert::error('Action Failed', 'Please select students from Exam Registration List');
        }
    }

    public function delete(Request $request)
    {
    }
}
