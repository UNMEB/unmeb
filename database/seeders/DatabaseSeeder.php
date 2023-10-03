<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Imports\CourseImport;
use App\Imports\CoursePaperImport;
use App\Imports\DistrictImport;
use App\Imports\ExamRegistrationImport;
use App\Imports\ExamRegistrationPeriodImport;
use App\Imports\InstitutionCourseImport;
use App\Imports\InstitutionImport;
use App\Imports\PaperImport;
use App\Imports\StudentImport;
use App\Imports\StudentRegistrationImport;
use App\Imports\StudentRegistrationPeriodImport;
use App\Imports\YearImport;
use App\Models\ExamRegistration;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create roles
        $this->call(RoleSeeder::class);

        // Create an admin user with the "admin" role and permissions
        User::factory()->admin()->create();

        $this->call(YearSeeder::class);
        $this->call(DistrictSeeder::class);
        $this->call(InstitutionSeeder::class);
        $this->call(CourseSeeder::class);
        $this->call(PaperSeeder::class);
        $this->call(InstitutionCourseSeeder::class);
        $this->call(CoursePaperSeeder::class);
        $this->call(UserSeeder::class);
        $this->call(StudentRegistrationPeriodSeeder::class);
        $this->call(ExamRegistrationPeriodSeeder::class);
        $this->call(SurchargeSeeder::class);
        $this->call(SurchargeFeeSeeder::class);
        // $this->call(StudentSeeder::class);
        // $this->call(StudentRegistrationSeeder::class);
        // $this->call(ExamRegistrationSeeder::class);

    }
}
