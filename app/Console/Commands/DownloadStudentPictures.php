<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use ZipArchive;
use App\Models\Student;

class DownloadStudentPictures extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:download-student-pictures';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download pictures of students and generate a zip file';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get list of student pictures
        $students = Student::whereNotNull('passport')
            ->whereNotNull('nsin')
            ->get();

        // Create a zip archive
        $zip = new ZipArchive;
        $zipFileName = 'student_pictures.zip';
        $zipFilePath = storage_path($zipFileName);

        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
            // Add files to the zip file
            foreach ($students as $student) {
                if (filter_var($student->passport, FILTER_VALIDATE_URL)) {
                    $response = Http::get($student->passport);
                    if ($response->successful()) {
                        $fileName = str_replace('/', '-', $student->nsin) . '.' . pathinfo($student->passport, PATHINFO_EXTENSION);
                        $zip->addFromString($fileName, $response->body());
                    }
                }
            }
            // Close the zip file
            $zip->close();

            // Print out the link to the zip file
            $link = asset('storage/' . $zipFileName);
            $this->info("Student pictures are zipped up. Download link: $link");
        } else {
            $this->error('Unable to create zip file.');
        }
    }
}
