<?php

namespace App\Imports;

use App\Models\Course;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CourseImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Course([
            'id' => $row['id'],
            'name' => $row['name'],
            'code' => $row['code'],
            'duration' => $row['duration'],
        ]);
    }

    public function chunkSize(): int
    {
        return 100;
    }
}
