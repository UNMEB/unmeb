<?php

namespace App\Console\Commands;

use App\Models\Registration;
use App\Models\Student;
use App\Models\StudentRegistration;
use App\Models\Transaction;
use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class RemoveMisplacedRegistrations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:remove-misplaced-registrations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove misplaced registrations and corresponding transactions.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $registrations = StudentRegistration::from('student_registrations as sr')
            ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->where('sr.created_at', '>', '2024-01-01')
            ->where('rp.flag', '!=', 1)
            ->get();

        $misplacedCount = 0;

        foreach ($registrations as $registration) {
            $student = Student::withoutGlobalScopes()->find($registration->student_id);
            if ($student) {
                $this->info('Found registration for student ' . $student->full_name .' in period: ' . $registration->reg_start_date . ' to ' . $registration->reg_end_date);
                $misplacedCount++;

                // Get the associated transaction e.g with comment = 'Exam Registration for student ID: {student_id}'
                $transaction = Transaction::withoutGlobalScopes()->where('comment', 'LIKE', 'Exam Registration for student ID: ' . $registration->student_id . '%')->first();
                
                if ($transaction) {
                    $this->info('Found transaction with comment ' . $transaction->comment);
                    // Delete the transaction
                    $transaction->delete();
                } else {
                    $this->info('No transaction found for student ID ' . $registration->student_id);
                }

                // Delete the student registration
                $registration->delete();
            } else {
                $this->info('No student found with ID ' . $registration->student_id);
            }
        }

        $this->info($misplacedCount . ' misplaced registrations and corresponding transactions removed successfully.');
    }
}
