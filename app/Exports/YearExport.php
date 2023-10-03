<?php

namespace App\Exports;

use App\Models\Year;
use Maatwebsite\Excel\Concerns\FromCollection;

class YearExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Year::all();
    }
}
