<?php

namespace App\Exports;

use App\Models\Paper;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaperExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Paper::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Paper Name',
            'Year Of Study',
            'Paper',
            'Abbreviation',
            'Paper Code',
        ];
    }
}
