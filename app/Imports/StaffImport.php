<?php

namespace App\Imports;

use App\Models\Staff;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class StaffImport implements ToModel, WithHeadingRow
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Staff([
            'institution_id' => $row['institution_id'],
            'name' => $row['name'],
            'designation' => $row['designation'],
            'status' => $row['status'],
            'education' => $row['education'],
            'qualification' => $row['qualification'],
            'council' => $row['council'],
            'reg_no' => $row['reg_no'],
            'reg_date' => $row['reg_date'],
            'lic_exp' => $row['lic_exp'],
            'experience' => $row['experience'],
            'telephone' => $row['telephone'],
            'email' => $row['email'],
            'bank' => $row['bank'],
            'branch' => $row['branch'],
            'acc_no' => $row['acc_no'],
            'acc_name' => $row['acc_name'],
        ]);
    }
}
