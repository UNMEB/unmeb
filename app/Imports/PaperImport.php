<?php

namespace App\Imports;

use App\Models\Paper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PaperImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Paper([
            'id' => $row['id'],
            'name' => $row['name'],
            'study_period' => $row['study_period'],
            'abbrev' => $row['abbrev'],
            'code' => $row['code'],
            'paper' => $row['paper']
        ]);
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
