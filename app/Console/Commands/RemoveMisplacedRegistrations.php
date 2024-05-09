<?php

namespace App\Console\Commands;

use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
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
        DB::beginTransaction();
        try {
            // Get all reversed NSIN transactions
            $reversedNSINTransactions = Transaction::withoutGlobalScopes()
                ->where('comment', 'LIKE', 'Reversal of NSIN Registration Fee for Student ID:%')
                ->orWhere('comment', 'LIKE', 'Reversal of Logbook Registration Fee for Student ID:%')
                ->orWhere('comment', 'LIKE', 'Reversal of Research Registration Fee for Student ID:%')
                ->get();

            $this->info('Found ' . $reversedNSINTransactions->count() . ' NSIN transactions ready to be reversed');

            foreach ($reversedNSINTransactions as $transaction) {
                $studentId = preg_match('/\d+/', $transaction->comment, $matches) ? $matches[0] : null;

                if ($studentId) {
                    $registration = NsinStudentRegistration::where('student_id', $studentId)
                        ->whereYear('created_at', now()->year)
                        ->first();

                    if (!$registration) {
                        $transaction->delete();
                        $this->info('Deleted transaction with ID ' . $transaction->id);
                    }
                } else {
                    $this->info('No student ID found for transaction with ID ' . $transaction->id);
                }
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }



}
