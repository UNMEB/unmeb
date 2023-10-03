<?php

namespace App\Imports;

use App\Models\Paper;
use Maatwebsite\Excel\Concerns\ToModel;

class PapersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Paper([
            //
        ]);
    }
}
