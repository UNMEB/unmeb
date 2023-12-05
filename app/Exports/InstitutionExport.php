<?php

namespace App\Exports;

use App\Models\Institution;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InstitutionExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Institution::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Short Name',
            'Institution Name',
            'Institution Location',
            'Institution Type',
            'Institution Code',
            'Institution Phone Number',
            'Institution Email Address',
            'Institution P.O.Box',
        ];
    }
}
