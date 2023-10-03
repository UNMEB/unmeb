<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DownloadReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'download:report {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download a report by name';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $name = $this->option('name');

        $columns = null;

        $this->info('Download data from remote database...');

        if ($name === 'exam_registration') {

            $columns = [
                'exam_registration_period_id',
                'institution_id',
                'course_id',
                'student_id',
                'is_active',
                'number_of_papers',
                'course_codes',
                'trial',
                'study_period',
                'is_completed',
                'is_approved',
                'is_verified',
                'remarks'
            ];



            $query = DB::connection('mysql2')
                ->table('students_registration')
                ->select([
                    'registration_period.registration_period_id AS exam_registration_period_id',
                    'institutions.institution_id',
                    'registration.course_id',
                    'students_registration.student_id AS student_id',
                    'students_registration.sr_flag AS is_active',
                    'students_registration.no_of_papers As number_of_papers',
                    'students_registration.course_codes',
                    'students_registration.trial',
                    'registration.year_of_study AS study_period',
                    'registration.completed AS is_completed',
                    'registration.approved AS is_approved',
                    'registration.verify AS is_verified',
                    'students_registration.remarks AS remarks'
                ])
                ->join('registration', 'students_registration.registration_id', '=', 'registration.registration_id')
                ->join('institutions', 'registration.institution_id', '=', 'institutions.institution_id')
                ->join('courses', 'registration.course_id', '=', 'courses.course_id')
                ->join('registration_period', 'registration.registration_period_id', '=', 'registration_period.registration_period_id');
        } else if ($name == 'student_registration') {

            $columns = [
                'institution_id',
                'course_id',
                'student_id',
                'month',
                'year_id',
                'is_completed',
                'is_approved',
                'is_book',
                'is_verified',
                'remarks'
            ];

            $query = DB::connection('mysql2')
                ->table('students_registration_nsin')
                ->select([
                    'nsinregistration.institution_id',
                    'nsinregistration.course_id',
                    'students_registration_nsin.student_id',
                    'nsinregistration.month',
                    'nsinregistration.year_id',
                    'nsinregistration.completed AS is_completed',
                    'nsinregistration.approved AS is_approved',
                    'nsinregistration.books AS is_book',
                    'students_registration_nsin.verify AS is_verified',
                    'students_registration_nsin.remarks'
                ])
                ->join('nsinregistration', 'students_registration_nsin.nsinregistration_id', '=', 'nsinregistration.nsinregistration_id');
        } else {
            $this->error('Invalid report name');
            return;
        }


        $batchSize = 1000000;
        $page = 1;
        do {

            $offset = ($page - 1) * $batchSize;

            $data = $query->offset($offset)
                ->limit($batchSize)
                ->get();

            if ($data->isEmpty()) {
                break; // No more data to retrieve
            }

            $fileName = $name . "_$page.csv";

            $filePath = public_path("imports/$fileName");

            $file = fopen($filePath, 'w');

            $this->info("Saving $fileName to public folder");

            fputcsv($file, $columns);

            foreach ($data as $row) {
                fputcsv($file, (array) $row);
            }

            fclose($file);

            $page++;
        } while (true);
    }
}
