<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NSINRegistrationExport implements FromCollection, WithHeadings
{
    protected $students;

    public function __construct($students)
    {
        $this->students = $students;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->students;
    }

    public function headings(): array
    {
        return [
            'Student ID',
            'Surname',
            'First Name',
            'Other Name',
            'Gender',
            'Date Of Birth',
            'District',
            'Country',
            'NSIN',
            'Phone Number',
        ];
    }
}
