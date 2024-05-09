<?php

namespace App\Console\Commands;

use App\Models\NsinRegistration;
use App\Models\NsinRegistrationPeriod;
use App\Models\NsinStudentRegistration;
use App\Models\RegistrationPeriod;
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
            $activeExamPeriod = RegistrationPeriod::whereFlag(1, true)->first();
            $year = Carbon::now()->year;

            $affectedExamTransactions = DB::table('transactions')
                ->where(function ($query) {
                    $query->where('comment', 'LIKE', 'Exam Registration for student ID:%')
                        ->whereDate('created_at', '<', '2024-05-06');
                })
                ->whereNotExists(function ($query) use ($activeExamPeriod, $year) {
                    $query->select(DB::raw(1))
                        ->from('student_registrations')
                        ->join('registrations', 'student_registrations.registration_id', '=', 'registrations.id')
                        ->where('student_registrations.student_id', DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(transactions.comment, ' ', -1),':',-1)"))
                        ->where('registrations.registration_period_id', $activeExamPeriod->id)
                        ->whereDate('student_registrations.created_at', '<', '2024-05-06');
                })
                ->get();

            $affectedExamTransactionIds = $affectedExamTransactions->pluck('id')->toArray();

            if (!empty($affectedExamTransactionIds)) {
                Transaction::withoutGlobalScopes()
                    ->whereIn('id', $affectedExamTransactionIds)->delete();
                $this->info('Deleted ' . count($affectedExamTransactionIds) . ' misplaced transactions successfully.');
            } else {
                $this->info('No misplaced transactions found.');
            }


            $activePeriod = NsinRegistrationPeriod::whereFlag(1, true)->first();


            $affectedTransactions = DB::table('transactions')
                ->where(function ($query) {
                    $query->where('comment', 'LIKE', 'NSIN Registration Fee for Student ID:%')
                        ->orWhere('comment', 'LIKE', 'Logbook Registration Fee for Student ID:%')
                        ->orWhere('comment', 'LIKE', 'Research Guideline Fee for Student ID:%')
                        ->whereDate('created_at', '<', '2024-05-06');
                })
                ->whereNotExists(function ($query) use ($activePeriod, $year) {
                    $query->select(DB::raw(1))
                        ->from('nsin_student_registrations')
                        ->join('nsin_registrations', 'nsin_student_registrations.nsin_registration_id', '=', 'nsin_registrations.id')
                        ->where('nsin_student_registrations.student_id', DB::raw("SUBSTRING_INDEX(SUBSTRING_INDEX(transactions.comment, ' ', -1),':',-1)"))
                        ->where('nsin_registrations.month', $activePeriod->month)
                        ->where('nsin_registrations.year_id', $activePeriod->year_id)
                        ->whereYear('nsin_student_registrations.created_at', $year);
                })
                ->get();

            $affectedTransactionIds = $affectedTransactions->pluck('id')->toArray();

            if (!empty($affectedTransactionIds)) {
                Transaction::withoutGlobalScopes()
                    ->whereIn('id', $affectedTransactionIds)->delete();
                $this->info('Deleted ' . count($affectedTransactionIds) . ' misplaced transactions successfully.');
            } else {
                $this->info('No misplaced transactions found.');
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
