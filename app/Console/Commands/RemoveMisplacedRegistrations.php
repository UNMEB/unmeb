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

        foreach ($registrations as $registration) {
            $deleted = StudentRegistration::where([
                'registration_id' => $registration->id,
                'student_id' => $registration->student_id,
            ])->delete();

            $this->info($deleted ? 'Record for ' . $registration->student_id .' deleted ':'Record for ' . $registration->student_id .' not deleted ');
        }
        $this->info($registrations->count() . ' misplaced registrations and corresponding transactions removed successfully.');
    }
}
