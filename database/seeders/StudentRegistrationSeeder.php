<?php

namespace Database\Seeders;

use App\Imports\StudentRegistrationImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class StudentRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $studentRegistrations = public_path('imports/student_registration_1.csv');
        Excel::import(new StudentRegistrationImport, $studentRegistrations);
    }
}
