<?php

namespace App\Exports;

use App\Models\Year;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class YearsExport implements FromCollection, WithHeadingRow
{

    use Exportable;

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Year::all();
    }
}
