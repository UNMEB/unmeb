<?php

namespace Database\Seeders;

use App\Imports\PaperImport;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;

class PaperSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $papers = public_path('imports/papers.csv');
        Excel::import(new PaperImport, $papers);
    }
}
