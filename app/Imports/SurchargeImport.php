<?php

namespace App\Imports;

use App\Models\Surcharge;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SurchargeImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Surcharge([
            'id' => $row['id'],
            'name' => $row['name'],
            'is_active' => $row['is_active']
        ]);
    }
}
