<?php

namespace App\Exports;

use App\Models\Institution;
use Maatwebsite\Excel\Concerns\FromCollection;

class InstitutionExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Institution::all();
    }
}
