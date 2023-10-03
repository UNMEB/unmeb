<?php

namespace Database\Seeders;

use App\Imports\DistrictImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = public_path('imports/districts.csv');
        Excel::import(new DistrictImport, $districts);
    }
}
