<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SurchargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ["id" => 1, "surchage_name" => "Normal", "s_flag" => 1],
            ["id" => 2, "surchage_name" => "Half", "s_flag" => 0],
            ["id" => 3, "surchage_name" => "Full", "s_flag" => 0]
        ];

        DB::table('surcharges')->insert($data);
    }
}