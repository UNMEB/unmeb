<?php

namespace App\Exports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CourseExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Course::all();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Program Name',
            'Program Code',
            'Program Duration',
            'Created On',
            'Last Updated',
        ];
    }
}
