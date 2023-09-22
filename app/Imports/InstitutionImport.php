<?php

namespace App\Imports;

use App\Models\District;
use App\Models\Institution;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use Illuminate\Support\Str;

class InstitutionImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // dd($row);

        $location = Str::upper($row['location']);

        $district = District::firstOrCreate([
            'name' => $location
        ]);



        return new Institution([
            'name' => $row['name'],
            'short_name' => $row['short_name'],
            'district_id' => $district->id,
            'type' => $row['type'],
            'code' => $row['code'],
            'phone' => $row['phone'],
            'box_no' => $row['box_no'],
        ]);
    }
}
