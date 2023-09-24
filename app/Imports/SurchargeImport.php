<?php

namespace App\Imports;

use App\Models\Surcharge;
use Maatwebsite\Excel\Concerns\ToModel;

class SurchargeImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Surcharge([
            //
        ]);
    }
}
