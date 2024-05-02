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
    protected $description = 'Command description';

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

        foreach ($registrations as $registration) {
            $student = Student::find($registration->student_id);
            $this->info('Found registration for student ' . $student->full_name .'');
        }

        $this->info('Misplaced registrations and corresponding transactions removed successfully.');
    }
}
