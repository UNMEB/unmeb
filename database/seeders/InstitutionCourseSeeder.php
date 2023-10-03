<?php

namespace Database\Seeders;

use App\Imports\InstitutionCourseImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class InstitutionCourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutionCourses = public_path('imports/institution_course.csv');
        Excel::import(new InstitutionCourseImport, $institutionCourses);
    }
}
