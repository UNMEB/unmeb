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
        $registrations = StudentRegistration::select('sr.student_id', 'r.id')
            ->from('student_registrations as sr')
            ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->where('sr.created_at', '>', '2024-01-01')
            ->where('rp.flag', '!=', 1)
            ->get();

        $registrationIds = $registrations->pluck('id')->toArray();
        $studentIds = $registrations->pluck('student_id')->toArray();

        $query = StudentRegistration::whereIn('registration_id', $registrationIds)
            ->whereIn('student_id', $studentIds);

        $sql = $query->toSql(); // Get SQL query

        // $deleted = $query->delete();

        $this->info($sql);
        // $this->info($deleted . ' misplaced registrations and corresponding transactions removed successfully.');
    }
}
