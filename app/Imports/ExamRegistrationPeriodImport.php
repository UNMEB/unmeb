<?php

namespace App\Imports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExamRegistrationPeriodImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {

            $rowData = [
                'start_date' => Carbon::createFromFormat('d/m/Y', $row['start_date'])->format('Y-m-d'),
                'end_date' => Carbon::createFromFormat('d/m/Y', $row['end_date'])->format('Y-m-d'),
                'academic_year' => $row['academic_year'],
                'is_active' => $row['is_active'],
            ];

            DB::table('exam_registration_periods')->insert($rowData);
        }
    }
}
