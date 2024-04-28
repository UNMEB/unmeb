<?php

namespace App\Orchid\Screens;

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
        // Check and handle null values for keys
        $registration_period_id = $request->get('registration_period_id');
        $registration_id = $request->get('registration_id');
        $institution_id = $request->get('institution_id');
        $course_id = $request->get('course_id');

        session()->put('registration_period_id', $registration_period_id);
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
            's.location',
            's.passport_number',
            's.nin',
            's.telephone',
            's.refugee_number',
            's.lin',
            's.nsin as nsin',
            's.passport'
        )
        ->from('registration_periods as rp')
        ->join('registrations AS r', 'rp.id','=','r.registration_period_id')
        ->join('student_registrations AS sr', 'r.id', '=','sr.registration_id')
        ->join('students as s', 'sr.student_id', '=','s.id')
        ->where('rp.id', $registration_period_id)
        ->where('r.institution_id', $institution_id)
        ->where('r.course_id', $course_id);

        if(auth()->user()->inRole('institution')) {
            $query->where('r.institution_id',  auth()->user()->institution_id);
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
        $registration_period_id = session()->get('registration_period_id');
        $registration_id = session()->get('registration_id');
        $institution_id = session()->get('institution_id');
        $course_id = session()->get('course_id');
    
        // Get Student IDs from form
        $studentIds = collect($request->get('students'))->values();
    
        foreach ($studentIds as $key => $studentId) {
    
            // Find the registration and delete it
            $registration = StudentRegistration::where([
                'student_id' => $studentId,
                'registration_id' => $registration_id,
            ])->delete();
    
            // Find the NSIN transaction and reverse it
            $examTransaction = Transaction::where('comment', '=', 'Exam Registration Fee for Student ID: ' . $studentId)->first();
    
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
    }

    public function delete(Request $request)
    {
    }
}
