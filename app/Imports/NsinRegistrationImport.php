<?php

namespace App\Imports;

use App\Models\RegistrationPeriodNsin;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class NsinRegistrationImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new RegistrationPeriodNsin([
            'id' => $row['id'],
            'year_id' => $row['year_id'],
            'month' => $row['month'],
            'flag' => $row['flag']
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
