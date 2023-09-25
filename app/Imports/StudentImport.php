<?php

namespace App\Imports;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Orchid\Attachment\File;

class StudentImport implements ToModel, WithHeadingRow, WithChunkReading
{

    public function __construct()
    {
    }

    public function model(array $row)
    {
        $dob = $row['dob'];
        if (!empty($row['dob'])) {
            try {
                $dob = Carbon::createFromFormat('m/d/Y', $row['dob'])->format('Y-m-d');
            } catch (\Exception $e) {
                // Handle the error, e.g., log it or set $dob to null
                $dob = null;
            }
        } else {
            // Handle the case where $row['dob'] is empty
            $dob = null;
        }


        $student = new Student([
            'id' => $row['id'],
            'nsin' => $row['nsin'],
            'surname' => $row['surname'],
            'firstname' => $row['firstname'],
            'othername' => $row['othername'],
            'gender' => $row['gender'],
            'dob' => $dob,
            'district_id' => $row['district_id'],
            'telephone' => $row['telephone'],
            'email' => $row['email'],
            'old_student' => $row['old_student']
        ]);

        $student->save();

        if ($row['photo']) {
            $photoDirectory = public_path('photos');
            $photoFilename = $row['photo'];
            $photoPath = $photoDirectory . '/' . $photoFilename;
            $photoPath = realpath($photoPath);

            if (file_exists($photoPath)) {
                $photo = new UploadedFile($photoPath, $row['photo']);
                $attachment = (new File($photo))->path('photos')->load();

                $student->photo = $attachment->url();

                $student->save();
                $student->attachment()->sync($attachment->id);
            }
        }

        return $student;
    }

    public function chunkSize(): int
    {
        return 5000;
    }
}
