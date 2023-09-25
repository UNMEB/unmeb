<?php

namespace App\Imports;

use App\Models\RegistrationPeriod;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExamRegistrationImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $startDate = !empty($row['start_date']) ? Carbon::createFromFormat('m/d/Y', $row['start_date'])->format('Y-m-d') : null;
        $endDate = !empty($row['end_date']) ? Carbon::createFromFormat('m/d/Y', $row['end_date'])->format('Y-m-d') : null;

        return new RegistrationPeriod([
            'id' => $row['id'],
            'start_date' => $startDate,
            'end_date' => $endDate,
            'academic_year' => $row['academic_year']
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
