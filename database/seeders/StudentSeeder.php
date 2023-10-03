<?php

namespace Database\Seeders;

use App\Imports\StudentImport;
use App\Jobs\StudentImportJob;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Excel as ExcelExcel;
use Maatwebsite\Excel\Facades\Excel;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = public_path('imports/students.csv');
        Excel::import(new StudentImport, $students, null, ExcelExcel::CSV);
    }
}
