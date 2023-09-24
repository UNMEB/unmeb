<?php

namespace App\Exports;

use App\Models\Surcharge;
use Maatwebsite\Excel\Concerns\FromCollection;

class SurchargeExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Surcharge::all();
    }
}
