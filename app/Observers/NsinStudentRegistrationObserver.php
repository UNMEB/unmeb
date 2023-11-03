<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinStudentRegistration;
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
        // Get the nsin registration
        $nsinRegistration = NsinRegistration::find($nsinStudentRegistration->nsin_registration_id);
        $studentId = $nsinStudentRegistration->student_id;
        $month = $nsinRegistration->month;
        $year = $nsinRegistration->year;
        $institutionId = $nsinRegistration->institution_id;
        $courseId = $nsinRegistration->course_id;

        // Get the institution
        $institution = Institution::find($institutionId);

        // Get the course
        $course = Course::find($courseId);


        // if sr_flag is set to 1 generate NSIN
        if ($nsinStudentRegistration->sr_flag == 1) {

            $nsinMonth = Str::upper(Str::limit($month, 3));
            $nsinYear = Str::substr($year->year, 2);
            $nsinInstituteCode = $institution->code;
            $nsinCourseCode = $course->course_code;

            // Generate the NSIN
            $nsin = $nsinMonth . '' . $nsinYear . '/' . $nsinInstituteCode . '/' . $nsinCourseCode . '/' . $studentId;

            $nsinStudentRegistration->NSIN = $nsin;
            $nsinStudentRegistration->save();
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
