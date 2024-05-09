<?php

namespace App\Console\Commands;

use App\Models\NsinRegistration;
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
        // Get all reversed NSIN transactions
        $reversedNSINTransactions = Transaction::withoutGlobalScopes()->where('comment', 'LIKE', 'Reversal of NSIN Registration Fee for Student ID:%')->get();

        $this->info('Found ' . $reversedNSINTransactions->count() . ' NSIN transactions ready to be reversed');

        // Get all reversed exam transactions
        $reversedExamTransactions = Transaction::withoutGlobalScopes()->where('comment', 'LIKE', 'Reversal of Exam Registration Fee for Student ID:%')->get();

        $this->info('Found ' . $reversedExamTransactions->count() . ' exam transactions ready to be reversed');

        // Extract student ids from comments for NSIN transactions and get student registrations
        $nsinStudentIds = $reversedNSINTransactions->map(function ($transaction) {
            preg_match('/\d+/', $transaction->comment, $matches);
            return $matches[0] ?? null;
        })->filter();

        $this->info('Found ' . $nsinStudentIds->count() . ' NSIN student IDs');

        $nsinStudentRegistrations = StudentRegistration::whereIn('student_id', $nsinStudentIds)
            ->whereYear('created_at', now()->year)
            ->get();

        // Extract student ids from comments for exam transactions and get student registrations
        $examStudentIds = $reversedExamTransactions->map(function ($transaction) {
            preg_match('/\d+/', $transaction->comment, $matches);
            return $matches[0] ?? null;
        })->filter();

        $this->info('Found ' . $examStudentIds->count() . ' exam student IDs');

        $examStudentRegistrations = StudentRegistration::whereIn('student_id', $examStudentIds)
            ->whereYear('created_at', now()->year)
            ->get();

        foreach ($nsinStudentRegistrations as $nsinStudentRegistration) {
            // Get the registration 
            $registrationId = $nsinStudentRegistration->registration_id;
            $registration = NsinRegistration::withoutGlobalScopes()->find($registrationId);
            dd($registration);
        }
    }


}
