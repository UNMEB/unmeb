<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NsinRegistrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ["id" => 13, "institution_id" => 19, "course_id" => 1, "amount" => 2280000, "receipt" => "", "month" => "November", "year_id" => 5, "completed" => 1, "approved" => 1, "books" => 1, "nsin" => 1, "nsin_verify" => 0, "old" => 0, "date_time" => "2018-08-15 10=>38=>05"],
            ["id" => 14, "institution_id" => 65, "course_id" => 10, "amount" => 40000, "receipt" => "", "month" => "November", "year_id" => 5, "completed" => 0, "approved" => 0, "books" => 1, "nsin" => 0, "nsin_verify" => 0, "old" => 1, "date_time" => "2021-07-20 16=>10=>35"],
            ["id" => 16, "institution_id" => 26, "course_id" => 1, "amount" => 20000, "receipt" => "", "month" => "November", "year_id" => 5, "completed" => 1, "approved" => 0, "books" => 1, "nsin" => 0, "nsin_verify" => 1, "old" => 1, "date_time" => "2018-08-15 10=>38=>05"],
            ["id" => 17, "institution_id" => 52, "course_id" => 3, "amount" => 660000, "receipt" => "", "month" => "November", "year_id" => 5, "completed" => 1, "approved" => 1, "books" => 1, "nsin" => 1, "nsin_verify" => 1, "old" => 0, "date_time" => "2018-08-15 10=>38=>05"],
        ];
        DB::table('nsin_registrations')->insert($data);
    }
}