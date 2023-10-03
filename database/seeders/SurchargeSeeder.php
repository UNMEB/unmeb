<?php

namespace Database\Seeders;

use App\Imports\SurchargeImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class SurchargeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $surcharges = public_path('imports/surcharges.csv');
        Excel::import(new SurchargeImport, $surcharges);
    }
}
