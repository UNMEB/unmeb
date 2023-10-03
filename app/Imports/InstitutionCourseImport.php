<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class InstitutionCourseImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $institutionId = $row['institution_id'];
            $courseId = $row['course_id'];

            // Check if institution_id and course_id exist in their respective tables
            $institutionExists = DB::table('institutions')
                ->where('id', $institutionId)
                ->exists();

            $courseExists = DB::table('courses')
                ->where('id', $courseId)
                ->exists();

            if ($institutionExists && $courseExists) {
                // Both institution_id and course_id exist, insert the record
                DB::table('institution_course')->insert([
                    'course_id' => $courseId,
                    'institution_id' => $institutionId,
                ]);
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
