<?php

namespace Database\Seeders;

use App\Imports\YearImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class YearSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $years = public_path('imports/years.csv');
        Excel::import(new YearImport, $years);
    }
}
