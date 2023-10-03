<?php

namespace Database\Seeders;

use App\Imports\CoursePaperImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class CoursePaperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $coursePapers = public_path('imports/course_paper.csv');
        Excel::import(new CoursePaperImport, $coursePapers);
    }
}
