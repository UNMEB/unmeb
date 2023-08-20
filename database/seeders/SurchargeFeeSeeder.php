<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SurchargeFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ["id" => 1, "surcharge_id" => 1, "course_id" => 1, "course_fee" => 200000],
            ["id" => 2, "surcharge_id" => 2, "course_id" => 1, "course_fee" => 300000],
            ["id" => 3, "surcharge_id" => 3, "course_id" => 1, "course_fee" => 400000],
            ["id" => 4, "surcharge_id" => 1, "course_id" => 3, "course_fee" => 200000],
            ["id" => 5, "surcharge_id" => 2, "course_id" => 3, "course_fee" => 300000],
            ["id" => 6, "surcharge_id" => 3, "course_id" => 3, "course_fee" => 400000],
            ["id" => 7, "surcharge_id" => 1, "course_id" => 4, "course_fee" => 200000],
            ["id" => 8, "surcharge_id" => 2, "course_id" => 4, "course_fee" => 300000],
            ["id" => 9, "surcharge_id" => 3, "course_id" => 4, "course_fee" => 300000],
            ["id" => 10, "surcharge_id" => 1, "course_id" => 5, "course_fee" => 230000],
            ["id" => 11, "surcharge_id" => 2, "course_id" => 5, "course_fee" => 345000],
            ["id" => 12, "surcharge_id" => 3, "course_id" => 5, "course_fee" => 460000],
            ["id" => 13, "surcharge_id" => 1, "course_id" => 6, "course_fee" => 230000],
            ["id" => 14, "surcharge_id" => 2, "course_id" => 6, "course_fee" => 345000],
            ["id" => 15, "surcharge_id" => 3, "course_id" => 6, "course_fee" => 460000],
            ["id" => 16, "surcharge_id" => 1, "course_id" => 7, "course_fee" => 230000],
            ["id" => 17, "surcharge_id" => 2, "course_id" => 7, "course_fee" => 345000],
            ["id" => 18, "surcharge_id" => 3, "course_id" => 7, "course_fee" => 460000],
            ["id" => 19, "surcharge_id" => 1, "course_id" => 12, "course_fee" => 230000],
            ["id" => 20, "surcharge_id" => 2, "course_id" => 12, "course_fee" => 345000],
            ["id" => 21, "surcharge_id" => 3, "course_id" => 12, "course_fee" => 460000],
            ["id" => 22, "surcharge_id" => 1, "course_id" => 14, "course_fee" => 230000],
            ["id" => 23, "surcharge_id" => 2, "course_id" => 14, "course_fee" => 345000],
            ["id" => 24, "surcharge_id" => 3, "course_id" => 14, "course_fee" => 460000],
            ["id" => 25, "surcharge_id" => 1, "course_id" => 11, "course_fee" => 230000],
            ["id" => 26, "surcharge_id" => 2, "course_id" => 11, "course_fee" => 345000],
            ["id" => 27, "surcharge_id" => 3, "course_id" => 11, "course_fee" => 460000],
            ["id" => 28, "surcharge_id" => 1, "course_id" => 15, "course_fee" => 230000],
            ["id" => 29, "surcharge_id" => 2, "course_id" => 15, "course_fee" => 345000],
            ["id" => 30, "surcharge_id" => 3, "course_id" => 15, "course_fee" => 460000],
            ["id" => 31, "surcharge_id" => 1, "course_id" => 8, "course_fee" => 230000],
            ["id" => 32, "surcharge_id" => 2, "course_id" => 8, "course_fee" => 345000],
            ["id" => 33, "surcharge_id" => 3, "course_id" => 8, "course_fee" => 460000],
            ["id" => 34, "surcharge_id" => 1, "course_id" => 10, "course_fee" => 230000],
            ["id" => 35, "surcharge_id" => 2, "course_id" => 10, "course_fee" => 345000],
            ["id" => 36, "surcharge_id" => 3, "course_id" => 10, "course_fee" => 460000],
            ["id" => 37, "surcharge_id" => 1, "course_id" => 9, "course_fee" => 230000],
            ["id" => 38, "surcharge_id" => 2, "course_id" => 9, "course_fee" => 345000],
            ["id" => 39, "surcharge_id" => 3, "course_id" => 9, "course_fee" => 460000],
            ["id" => 40, "surcharge_id" => 1, "course_id" => 13, "course_fee" => 230000],
            ["id" => 41, "surcharge_id" => 2, "course_id" => 13, "course_fee" => 345000],
            ["id" => 42, "surcharge_id" => 3, "course_id" => 13, "course_fee" => 460000],
            ["id" => 43, "surcharge_id" => 1, "course_id" => 16, "course_fee" => 230000],
            ["id" => 44, "surcharge_id" => 2, "course_id" => 16, "course_fee" => 345000],
            ["id" => 45, "surcharge_id" => 3, "course_id" => 16, "course_fee" => 460000],
            ["id" => 46, "surcharge_id" => 1, "course_id" => 2, "course_fee" => 200000],
            ["id" => 47, "surcharge_id" => 2, "course_id" => 2, "course_fee" => 300000],
            ["id" => 48, "surcharge_id" => 3, "course_id" => 2, "course_fee" => 400000],
            ["id" => 49, "surcharge_id" => 1, "course_id" => 18, "course_fee" => 230000],
            ["id" => 50, "surcharge_id" => 2, "course_id" => 18, "course_fee" => 345000],
            ["id" => 51, "surcharge_id" => 3, "course_id" => 18, "course_fee" => 460000]
        ];

        DB::table('surcharge_fees')->insert($data);
    }
}