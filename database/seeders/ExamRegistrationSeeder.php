<?php

namespace Database\Seeders;

use App\Imports\ExamRegistrationImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class ExamRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $examRegistration = public_path('imports/exam_registration_1.csv');
        Excel::import(new ExamRegistrationImport, $examRegistration);
    }
}
