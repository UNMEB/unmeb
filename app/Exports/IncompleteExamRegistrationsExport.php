<?php

namespace App\Exports;

use App\Models\Registration;
use App\Models\StudentRegistration;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class IncompleteExamRegistrationsExport implements FromCollection, WithHeadings
{

    use Exportable;

    public function collection()
    {
        $data = Registration::query()
            ->from('registrations as r')
            ->join('registration_periods as rp', 'r.registration_period_id', '=', 'rp.id')
            ->join('institutions as i', 'r.institution_id', '=', 'i.id')
            ->join('courses as c', 'r.course_id', '=', 'c.id')
            ->select('i.id AS institution_id', 'i.institution_name', 'r.id as registration_id', 'c.id as course_id', 'c.course_name', 'rp.id as registration_period_id', 'rp.reg_start_date', 'rp.reg_end_date', 'r.completed', 'r.verify', 'r.approved')
            ->groupBy('i.id', 'i.institution_name', 'r.id', 'c.course_name', 'rp.id', 'rp.reg_start_date', 'rp.reg_end_date', 'r.completed', 'r.verify', 'r.approved')
            ->orderBy('r.updated_at', 'desc')
            ->get();

        $data = $data->map(function ($item) {
            $regs1 = StudentRegistration::query()
                ->where('registration_id', $item->registration_id)
                ->count();

            $regs2 = StudentRegistration::query()
                ->where('registration_id', $item->registration_id)
                ->where('sr_flag', 0)
                ->count();

            return [
                'institution_name' => $item->institution_name,
                'course_name' => $item->course_name,
                'total_students' => $regs1,
                'pending_students' => $regs2
            ];
        });

        return $data;

    }

    public function headings(): array
    {
        // Define your headers here
        return [
            'Institution Name',
            'Course Name',
            'Total Students',
            'Pending Students'
        ];
    }
}
