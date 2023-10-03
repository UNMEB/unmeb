<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StudentRegistrationPeriodImport implements ToCollection, WithHeadingRow
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {

            $rowData = [
                'month' => $row['month'],
                'year_id' => $row['year_id'],
                'is_active' => $row['is_active'],
            ];

            DB::table('student_registration_periods')->insert($rowData);
        }
    }
}
