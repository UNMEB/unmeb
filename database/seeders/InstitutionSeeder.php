<?php

namespace Database\Seeders;

use App\Imports\InstitutionImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class InstitutionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $institutions = public_path('imports/institutions.csv');
        Excel::import(new InstitutionImport, $institutions);
    }
}
