<?php

namespace Database\Seeders;

use App\Imports\SurchargeFeeImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class SurchargeFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $surchargeFees = public_path('imports/surcharge_fees.csv');
        Excel::import(new SurchargeFeeImport, $surchargeFees);
    }
}
