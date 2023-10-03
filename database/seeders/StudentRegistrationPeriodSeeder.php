<?php

namespace Database\Seeders;

use App\Imports\StudentRegistrationPeriodImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class StudentRegistrationPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentRegistrationPeriods = public_path('imports/student_registration_periods.csv');
        Excel::import(new StudentRegistrationPeriodImport, $studentRegistrationPeriods);
    }
}
