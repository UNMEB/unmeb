<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ["id" => 1, "course_name" => "Certificate in Comprehensive Nursing", "course_code" => "CCN", "duration" => 3],
            ["id" => 2, "course_name" => "Certificate in Mental Health Nursing", "course_code" => "CMHN", "duration" => 3],
            ["id" => 3, "course_name" => "Certificate in Midwifery", "course_code" => "CM", "duration" => 3],
            ["id" => 4, "course_name" => "Certificate in Nursing", "course_code" => "CN", "duration" => 3],
            ["id" => 5, "course_name" => "Diploma in Comprehensive Nursing", "course_code" => "DCN", "duration" => 3],
            ["id" => 6, "course_name" => "Diploma in Mental Health Nursing", "course_code" => "DMHN", "duration" => 3],
            ["id" => 7, "course_name" => "Diploma in Midwifery", "course_code" => "DMD", "duration" => 3],
            ["id" => 8, "course_name" => "Diploma in Nursing", "course_code" => "DND", "duration" => 3],
            ["id" => 9, "course_name" => "Diploma in Paediatrics Nursing and Child Health", "course_code" => "DPCHN", "duration" => 3],
            ["id" => 10, "course_name" => "Diploma in Nursing -Extension", "course_code" => "DNE", "duration" => 3],
            ["id" => 11, "course_name" => "Diploma in Midwifery -Extension", "course_code" => "DME", "duration" => 3],
            ["id" => 12, "course_name" => "Diploma in Comprehensive Nursing -Extension", "course_code" => "DCNE", "duration" => 3],
            ["id" => 13, "course_name" => "Diploma in Peadiatric Nursing -Extension", "course_code" => "DPCHNE", "duration" => 3],
            ["id" => 14, "course_name" => "Diploma in Mental Health Nursing -Extension", "course_code" => "DMHNE", "duration" => 3],
            ["id" => 15, "course_name" => "Diploma in Midwifery  E-Learning", "course_code" => "DMEL", "duration" => 3],
            ["id" => 16, "course_name" => "Diploma in Public Health Nursing ", "course_code" => "DPHN", "duration" => 3],
            ["id" => 17, "course_name" => "Diploma in Palliative Care Nursing", "course_code" => "DPCN", "duration" => 1],
            ["id" => 18, "course_name" => "Advanced Diploma in Palliative Care Nursing", "course_code" => "ADPCN", "duration" => 1]
        ];

        DB::table('courses')->insert($data);
    }
}
