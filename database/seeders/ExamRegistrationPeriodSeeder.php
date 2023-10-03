<?php

namespace Database\Seeders;

use App\Imports\ExamRegistrationPeriodImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class ExamRegistrationPeriodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if file exists else throw error and fail seeder
        $examRegistrationPeriods = public_path('imports/exam_registration_periods.csv');

        if (!file_exists($examRegistrationPeriods)) {
            throw new \Exception('Exam registration periods file does not exist');
        }

        Excel::import(new ExamRegistrationPeriodImport, $examRegistrationPeriods);
    }
}
