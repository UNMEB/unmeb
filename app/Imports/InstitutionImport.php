<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\Institution;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Str;

class InstitutionImport implements ToModel, WithHeadingRow, WithChunkReading
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $institution = new Institution([
            'id' => $row['id'],
            'name' => Str::title(Str::lower($row['name'])),
            'short_name' => Str::upper($row['short_name']),
            'location' => Str::title(Str::lower($row['location'])),
            'type' => $row['type'],
            'code' => $row['code'],
            'phone_no' => $row['phone_no'],
            'box_no' => $row['box_no'],
        ]);

        $institution->save();

        $account = new Account();
        $account->institution_id = $institution->id;
        $account->balance = 0;
        $account->save();

        return $institution;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
