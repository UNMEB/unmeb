<?php

namespace App\Imports;

use App\Models\SurchargeFee;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SurchargeFeeImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new SurchargeFee([
            'id' => $row['id'],
            'surcharge_id' => $row['surcharge_id'],
            'course_id' => $row['course_id'],
            'course_fee' => $row['course_fee']
        ]);
    }
}
