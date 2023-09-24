<?php

namespace App\Imports;

use App\Models\Student;
use Maatwebsite\Excel\Concerns\ToModel;

class StudentImport implements ToModel
{
    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        return new Student([
            'nsin' => $row['nsin'],
            'surname' => $row['surname'],
            'firstname' => $row['firstname'],
            'othername' => $row['othername'],
            'photo' => $row['photo'],
            'gender' => $row['gender'],
            'dob' => $row['dob'],
            'district_id' => $row['district_id'],
            'telephone' => $row['telephone'],
            'email' => $row['email'],
            'old_student' => $row['old_student'],
        ]);
    }
}
