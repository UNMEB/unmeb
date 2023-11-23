<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NsinStudentRegistrationObserver
{
    /**
     * Handle the NsinStudentRegistration "created" event.
     */
    public function created(NsinStudentRegistration $nsinStudentRegistration): void
    {
        //
    }

    /**
     * Handle the NsinStudentRegistration "updated" event.
     */
    public function updated(NsinStudentRegistration $nsinStudentRegistration): void
    {
        Log::info('NSIN Registration Updated', $nsinStudentRegistration->toArray());

        $studentId = $nsinStudentRegistration->student_id;

        $student = Student::find($studentId);
        

        // Get the NSIN Registration Information
        $nsinRegistration = NsinRegistration::find($nsinStudentRegistration->nsin_registration_id);
        $month = $nsinRegistration->month;
        $year = $nsinRegistration->year;
        $institutionId = $nsinRegistration->institution_id;
        $courseId = $nsinRegistration->course_id;
        // Get the institution
        $institution = Institution::find($institutionId);

        // Get the course
        $course = Course::find($courseId);

        // If verify is true, generate NSIN
        if ($nsinStudentRegistration->verify == 1) {
            // Generate NSIN code here
            Log::info('NSIN Registration Verified :: Generating NSIN', $nsinStudentRegistration->toArray());

            $nsinMonth = Str::upper(Str::substr($month, 0, 3));
            $nsinYear = Str::substr($year->year, -2);
            $nsinInstituteCode = $institution->code;
            $nsinCourseCode = $course->course_code;

            // Generate the NSIN
            $nsin = $nsinMonth . '' . $nsinYear . '/' . $nsinInstituteCode . '/' . $nsinCourseCode . '/' . $studentId;

            Log::info('NSIN Registration Verified :: NSIN Generated', [
                'nsin' => $nsin,
            ]);

            $nsinStudentRegistration->nsin = $nsin;
            $nsinStudentRegistration->save();

            // Update the student record with this NSIN
            $student->nsin = $nsin;
            $student->save();

        }
    }

    /**
     * Handle the NsinStudentRegistration "deleted" event.
     */
    public function deleted(NsinStudentRegistration $nsinStudentRegistration): void
    {
        //
    }

    /**
     * Handle the NsinStudentRegistration "restored" event.
     */
    public function restored(NsinStudentRegistration $nsinStudentRegistration): void
    {
        //
    }

    /**
     * Handle the NsinStudentRegistration "force deleted" event.
     */
    public function forceDeleted(NsinStudentRegistration $nsinStudentRegistration): void
    {
        //
    }
}
