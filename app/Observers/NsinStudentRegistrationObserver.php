<?php

namespace App\Observers;

use App\Models\Account;
use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Models\StudentPaperRegistration;
use App\Models\Transaction;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NsinStudentRegistrationObserver
{
    /**
     * Handle the NsinStudentRegistration "created" event.
     */
    public function created(NsinStudentRegistration $nsinStudentRegistration): void
    {
        //
    }

    /**
     * Handle the NsinStudentRegistration "updated" event.
     */
    public function updated(NsinStudentRegistration $nsinStudentRegistration): void
    {

        // Check if the observer should proceed
        if ($nsinStudentRegistration->is_observer_triggered) {
            return;
        }

        // Set the flag to true to prevent recursive calls
        $nsinStudentRegistration->is_observer_triggered = true;

        // Eager load the nsinRegistration with necessary fields, including the related year
        $nsinRegistration = $nsinStudentRegistration->load([
            'nsinRegistration' => function ($query) {
                $query->select('id', 'month', 'year_id', 'institution_id', 'course_id')
                    ->with('year:id,year'); // Assuming 'year' is the relationship name and 'year' is the field in the years table
            }
        ])->nsinRegistration;

        // if verify is set to 1 generate NSIN
        if ($nsinStudentRegistration->verify == 1) {
            // Use eager loaded relations and select only necessary fields
            $institutionCode = Institution::where('id', $nsinRegistration->institution_id)->value('code');
            $courseCode = Course::where('id', $nsinRegistration->course_id)->value('course_code');

            $nsinMonth = Str::upper(Str::limit($nsinRegistration->month, 3, ''));
            $nsinYear = Str::substr($nsinRegistration->year->year, 2); // Accessing year from the eager loaded relationship

            // Generate the NSIN using interpolation
            $nsin = "{$nsinMonth}{$nsinYear}/{$institutionCode}/{$courseCode}/{$nsinStudentRegistration->student_code}";

            $nsinStudentRegistration->nsin = $nsin;
            $nsinStudentRegistration->saveQuietly();

            $studentId = $nsinStudentRegistration->student_id;

            $student = Student::where("id", $studentId)->first();
            if ($student) {
                $student->nsin = $nsin;
                $student->saveQuietly();
            }

            // Reset the flag
            $nsinStudentRegistration->is_observer_triggered = false;
        }

        // if verify is set to 2 the reverse the account balance by creating a credit transaction
        else if ($nsinStudentRegistration->verify == 2) {
            // $account = Account::where('institution_id', $nsinRegistration->institution_id)->first();

            $studentId = $nsinStudentRegistration->student_id;


            // Find the original transaction
            $transaction = Transaction::where('comment', '=', 'NSIN Registration Fee for Student ID: ' . $studentId)->first();

            if ($transaction) {
                // Create a new transaction to record the reversal
                $reversalTransaction = new Transaction([
                    'amount' => $transaction->amount, // reverse the amount
                    'type' => 'credit', // credit the reversed amount
                    'account_id' => $transaction->account_id,
                    'institution_id' => $transaction->institution_id,
                    'initiated_by' => auth()->user()->id,
                    'status' => 'approved',
                    'comment' => 'Reversal of NSIN Registration Fee for Student ID: ' . $studentId,
                ]);
                $reversalTransaction->save();

                // Update the original transaction to mark it as reversed
                $transaction->status = 'reversed';
                $transaction->save();

                // Update account balance with increment
                $account = $transaction->account;
                $account->balance += $transaction->amount; // Increment the balance by the original transaction amount
                $account->save();
            }

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

        }

    }

    /**
     * Handle the NsinStudentRegistration "deleted" event.
     */
    public function deleted(NsinStudentRegistration $nsinStudentRegistration): void
    {
        //
    }

    /**
     * Handle the NsinStudentRegistration "restored" event.
     */
    public function restored(NsinStudentRegistration $nsinStudentRegistration): void
    {
        //
    }

    /**
     * Handle the NsinStudentRegistration "force deleted" event.
     */
    public function forceDeleted(NsinStudentRegistration $nsinStudentRegistration): void
    {
        //
    }
}
