<?php

namespace Database\Seeders;

use App\Imports\CourseImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = public_path('imports/courses.csv');
        Excel::import(new CourseImport, $courses);

    }
}
