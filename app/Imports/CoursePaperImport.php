<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CoursePaperImport implements ToCollection, WithHeadingRow, WithChunkReading
{
    /**
     * @param Collection $collection
     */
    public function collection(Collection $collection)
    {
        foreach ($collection as $row) {
            $courseId = $row['course_id'];
            $paperId = $row['paper_id'];

            // Check if course_id and paper_id exist in their respective tables
            $courseExists = DB::table('courses')
                ->where('id', $courseId)
                ->exists();

            $paperExists = DB::table('papers')
                ->where('id', $paperId)
                ->exists();

            if ($courseExists && $paperExists) {
                // Both course_id and paper_id exist, insert the record
                DB::table('course_paper')->insert([
                    'id' => $row['id'],
                    'course_id' => $courseId,
                    'paper_id' => $paperId,
                ]);
            }
        }
    }

    public function chunkSize(): int
    {
        return 500;
    }
}
