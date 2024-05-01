<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExamApplicationExport implements FromCollection, WithHeadings
{
    protected $students;
    
    public function __construct(Collection $students)
    {
        $this->students = $students;
    }
    /**
     * @return Collection
     */
    public function collection() {
        return $this->students;
    }
    
    /**
     * @return array
     */
    public function headings(): array {
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
            'Trial',
            'Course Codes',
            'No Of Papers'
        ];
    }
}
