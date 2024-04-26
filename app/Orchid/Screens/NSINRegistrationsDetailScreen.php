<?php

namespace App\Orchid\Screens;

use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Models\Transaction;
use App\Orchid\Layouts\NSINRegistrationTable;
use Illuminate\Http\Request;
use Orchid\Screen\Actions\Button;
use Orchid\Screen\Actions\DropDown;
use Orchid\Screen\Fields\CheckBox;
use Orchid\Screen\Fields\Group;
use Orchid\Screen\Fields\Input;
use Orchid\Screen\Screen;
use Orchid\Screen\TD;
use Orchid\Support\Facades\Layout;
use RealRashid\SweetAlert\Facades\Alert;

class NSINRegistrationsDetailScreen extends Screen
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
        

        $query = NsinRegistrationPeriod::select(
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
        ->from('nsin_registration_periods as rp')
        ->join('nsin_registrations AS r', function ($join)  {
            $join->on('rp.month','=','r.month');
            $join->on('rp.year_id','=','r.year_id');
        })
        ->join('nsin_student_registrations AS sr', 'r.id', '=','sr.nsin_registration_id')
        ->join('students as s', 'sr.student_id', '=','s.id')
        ->where('rp.id', $registration_period_id)
        ->where('r.institution_id', $institution_id)
        ->where('r.course_id', $course_id);

        

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
        return 'NSIN Registrations';
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
                Button::make('Rollback NSIN Registration')
                ->icon('bs.receipt')
                ->class('btn link-success')
                ->method('rollback'),

                Button::make('Delete NSINs')
                ->icon('bs.trash3')
                ->confirm(__('Once you confirm, all NSINs will be deleted for the current period'))
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
        $table = (new NSINRegistrationTable);
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
            $registration = NsinStudentRegistration::where([
                'student_id' => $studentId,
                'nsin_registration_id' => $registration_id,
            ])->delete();

            // Find the logbook transaction for this student
            $logbookTransaction = Transaction::where('comment', '=', 'Logbook Fee for Student ID: ' . $studentId)->first();

            if ($logbookTransaction) {
                // Create a new transaction to record the reversal
                $reversalLogbookTransaction = new Transaction([
                    'amount' => -$logbookTransaction->amount, // reverse the amount
                    'type' => 'credit', // credit the reversed amount
                    'account_id' => $logbookTransaction->account_id,
                    'institution_id' => $logbookTransaction->institution_id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'Reversal of Logbook Fee for Student ID: ' . $studentId,
                ]);
                $reversalLogbookTransaction->save();

                // Update the original transaction to mark it as reversed
                $logbookTransaction->status = 'reversed';
                $logbookTransaction->save();

                // Update account balance with increment
                $account = $logbookTransaction->account;
                $account->balance += $logbookTransaction->amount; // Increment the balance by the original transaction amount
                $account->save();
            }

            // Find the NSIN transaction and reverse it
            $nsinTransaction = Transaction::where('comment', '=', 'NSIN Registration Fee for Student ID: ' . $studentId)->first();

            if ($nsinTransaction) {
                // Create a new transaction to record the reversal
                $reversalTransaction = new Transaction([
                    'amount' => $nsinTransaction->amount, // reverse the amount
                    'type' => 'credit', // credit the reversed amount
                    'account_id' => $nsinTransaction->account_id,
                    'institution_id' => $nsinTransaction->institution_id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'Reversal of NSIN Registration Fee for Student ID: ' . $studentId,
                ]);
                $reversalTransaction->save();

                // Update the original transaction to mark it as reversed
                $nsinTransaction->status = 'reversed';
                $nsinTransaction->save();
            }

            // Find student record and replace NSIN with null
            Student::where('id', $studentId)->update([
                'nsin' => null
            ]);
        }

        Alert::success('NSINs Recalled', count($studentIds) . ' NSINs successfully recalled and transactions reversed.')->persistent(true);
    }

    public function delete(Request $request)
    {
    }
}
