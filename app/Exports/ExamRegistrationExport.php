<?php

namespace App\Exports;

use App\Models\RegistrationPeriod;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamRegistrationExport implements FromCollection, WithHeadings
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return RegistrationPeriod::all();
    }

    public function headings(): array
    {
        return [
            'id' => 'ID',
            'academic_year' => 'Academic Year',
            'start_date' => 'Start Date',
            'end_date' => 'End Date'
        ];
    }
}
