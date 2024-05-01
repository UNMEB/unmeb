<?php

namespace App\Exports;

use App\Models\Student;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class NSINApplicationExport implements FromCollection, WithHeadings
{
    protected $students;

    public function __construct(Collection $students)
    {
        $this->students = $students;
    }

    public function collection()
    {
        return $this->students;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Surname',
            'Firstname',
            'Othername',
            'Gender',
            'DOB',
            'District',
            'Country',
            'NSIN',
            'Telephone',
            'Passport',
            'Passport Number',
            'LIN',
            'Email'
        ];
    }
}
