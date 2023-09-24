<?php

namespace App\Imports;

use App\Models\Paper;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PaperImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Paper([
            'name' => $row['name'],
            'year' => $row['year'],
            'paper' => $row['paper'],
            'abbrev' => $row['abbrev'],
            'code' => $row['code']
        ]);
    }
}
