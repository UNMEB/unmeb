<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CreateRegistrationReportDaily extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-registration-report-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate daily registration report and save to Redis';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = DB::table('student_registrations as sr')
            ->join('registrations as r', 'r.id', '=', 'sr.registration_id')
            ->join('registration_periods as rp', 'rp.id', '=', 'r.registration_period_id')
            ->join('institutions as i', 'i.id', '=', 'r.institution_id')
            ->join('courses as c', 'c.id', '=', 'r.course_id')
            ->join('students as s', 's.id', '=', 'sr.student_id')
            ->select(
                'i.institution_name as institution',
                'c.course_name as course',
                'r.year_of_study',
                DB::raw('COUNT(sr.student_id) as total_students'),
                DB::raw('SUM(CASE WHEN s.gender = "Male" THEN 1 ELSE 0 END) as male_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Female" THEN 1 ELSE 0 END) as female_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Male" AND sr.sr_flag = 1 THEN 1 ELSE 0 END) as male_approved_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Female" AND sr.sr_flag = 1 THEN 1 ELSE 0 END) as female_approved_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Male" AND sr.sr_flag = 2 THEN 1 ELSE 0 END) as male_rejected_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Female" AND sr.sr_flag = 2 THEN 1 ELSE 0 END) as female_rejected_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Male" AND sr.sr_flag = 0 THEN 1 ELSE 0 END) as male_pending_count'),
                DB::raw('SUM(CASE WHEN s.gender = "Female" AND sr.sr_flag = 0 THEN 1 ELSE 0 END) as female_pending_count'),
                DB::raw('SUM(CASE WHEN sr.sr_flag = 0 THEN 1 ELSE 0 END) as pending_count'),
                DB::raw('SUM(CASE WHEN sr.sr_flag = 1 THEN 1 ELSE 0 END) as approved_count'),
                DB::raw('SUM(CASE WHEN sr.sr_flag = 2 THEN 1 ELSE 0 END) as rejected_count')
            )
            ->where('rp.flag', '=', 1)
            ->groupBy('i.institution_name', 'c.course_name', 'r.year_of_study')
            ->orderBy('i.institution_name')
            ->get();

        // Convert result to array
        $data = $result->toArray();

        // Save data to Redis
        Redis::set('registration_report_daily', json_encode($data));

        $this->info('Daily registration report generated and saved to Redis successfully!');
    }
}
