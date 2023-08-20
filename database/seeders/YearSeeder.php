<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class YearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            ["id" => 1, "year" => 2011, "flag" => 1],
            ["id" => 2, "year" => 2012, "flag" => 1],
            ["id" => 3, "year" => 2013, "flag" => 1],
            ["id" => 4, "year" => 2014, "flag" => 1],
            ["id" => 5, "year" => 2015, "flag" => 1],
            ["id" => 6, "year" => 2016, "flag" => 1],
            ["id" => 7, "year" => 2017, "flag" => 1],
            ["id" => 8, "year" => 2018, "flag" => 1],
            ["id" => 9, "year" => 2019, "flag" => 1],
            ["id" => 10, "year" => 2020, "flag" => 1],
            ["id" => 11, "year" => 2021, "flag" => 1],
            ["id" => 12, "year" => 2022, "flag" => 1],
            ["id" => 13, "year" => 2023, "flag" => 1],
            ["id" => 99, "year" => 0, "flag" => 1],
        ];

        DB::table('years')->insert($data);
    }
}