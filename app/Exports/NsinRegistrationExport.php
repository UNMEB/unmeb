<?php

namespace App\Exports;

use App\Models\RegistrationPeriodNsin;
use Maatwebsite\Excel\Concerns\FromCollection;

class NsinRegistrationExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return RegistrationPeriodNsin::all();
    }
}
