<?php

namespace App\Imports;

use App\Models\Year;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class YearsImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Year([
            "name" => $row["name"],
            "flag" => $row["flag"]
        ]);
    }
}
