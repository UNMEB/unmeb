<?php

namespace App\Exports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IncompleteNsinRegistrationsExport implements FromCollection, WithHeadings
{

    public $institutionId;
    public $courseId;
    public $nsinRegistrationId;

    public function __construct($institutionId, $courseId, $nsinRegistrationId)
    {
        $this->institutionId = $institutionId;
        $this->courseId = $courseId;
        $this->nsinRegistrationId = $nsinRegistrationId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Student::select(
            'students.id',
            'institutions.institution_name',
            'courses.course_name',
            'nsin_registrations.id AS nsin_registration_id',
            'students.*'
        )
            ->join('nsin_student_registrations', 'students.id', '=', 'nsin_student_registrations.student_id')
            ->join('nsin_registrations', 'nsin_student_registrations.nsin_registration_id', '=', 'nsin_registrations.id')
            ->join('institutions', 'nsin_registrations.institution_id', '=', 'institutions.id')
            ->join('courses', 'nsin_registrations.course_id', '=', 'courses.id')
            ->where('nsin_registrations.id', $this->nsinRegistrationId)
            ->where('institutions.id', $this->institutionId)
            ->where('courses.id', $this->courseId)
            ->get()
            ->map(function ($student) {
              return [
                'id'=> $student->id,
                'name' => $student->full_name,
                'gender' => $student->gender,
                'district' => $student->district->district_name,
                'telephone' => $student->telephone,
                'email' => $student->email,
                'date_time' => $student->date_time
              ];  
            });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Student Name',
            'Gender',
            'District',
            'Phone Number',
            'Email Address',
            'Registration Date'
        ];
    }

}
