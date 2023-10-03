<?php

namespace App\Imports;

use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Orchid\Attachment\File;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class StudentImport implements
    ToModel,
    WithHeadingRow,
    WithChunkReading,
    WithBatchInserts
{
    use Importable;

    public function __construct()
    {
    }

    public function model(array $row)
    {
        // $existingStudent = null;

        // // Check if the email is not empty or null
        // if (!empty($row['email'])) {
        //     $existingStudent = Student::where('email', $row['email'])->first();
        // }

        // // Check if a student with the same NSIN already exists
        // $existingStudentByNSIN = Student::where('nsin', $row['nsin'])->first();

        // // Skip importing if either email or NSIN already exists
        // if ($existingStudent || $existingStudentByNSIN) {
        //     return null;
        // }

        // $dob = $row['dob'];

        // // Use regular expression to validate the date format (m/d/Y)
        // if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $dob)) {
        //     try {
        //         $dob = Carbon::createFromFormat('m/d/Y', $dob);
        //     } catch (\Exception $e) {
        //         // Handle the error, e.g., log it or set $dob to null
        //         $dob = null;
        //     }
        // } else {
        //     // Invalid date format, set $dob to null
        //     $dob = null;
        // }

        $student = new Student([
            'id' => $row['id'],
            'nsin' => empty($row['nsin']) ? null : Str::of($row['nsin'])->replace(' ', '')->upper(),
            'surname' => Str::of($row['surname'])->trim()->title(),
            'firstname' => Str::of($row['firstname'])->trim()->title(),
            'othername' => Str::of($row['othername'])->trim()->title(),
            'gender' => $row['gender'],
            'dob' => $row['dob'],
            'district_id' => $row['district_id'],
            'address' => $row['address'],
            'telephone' => $row['telephone'],
            'email' => empty($row['email']) ? null : Str::of($row['email'])->trim()->lower(),
            'old' => $row['old']
        ]);

        $student->save();

        if ($row['passport']) {
            $photoDirectory = public_path('photos');
            $photoFilename = $row['passport'];
            $photoPath = $photoDirectory . '/' . $photoFilename;
            $photoPath = realpath($photoPath);

            if (file_exists($photoPath)) {
                $photo = new UploadedFile($photoPath, $row['passport']);
                $attachment = (new File($photo))->path('photos')->load();

                $student->passport = $attachment->url();

                $student->save();
                $student->attachment()->sync($attachment->id);
            }
        }

        return $student;
    }

    public function chunkSize(): int
    {
        return 1000;
    }

    public function batchSize(): int
    {
        return 5;
    }
}
