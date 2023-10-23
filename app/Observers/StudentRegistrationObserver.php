<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\StudentRegistration;
use Illuminate\Support\Str;

class StudentRegistrationObserver
{
    /**
     * Handle the StudentRegistration "created" event.
     */
    public function created(StudentRegistration $studentRegistration): void
    {
    }

    /**
     * Handle the StudentRegistration "updated" event.
     */
    public function updated(StudentRegistration $studentRegistration): void
    {
        // Get the nsin registration
        $nsinRegistration = NsinRegistration::find($studentRegistration->nsin_registration_id);
        $studentId = $studentRegistration->student_id;
        $month = $nsinRegistration->month;
        $year = $nsinRegistration->year;
        $institutionId = $nsinRegistration->institution_id;
        $courseId = $nsinRegistration->course_id;

        // Get the institution
        $institution = Institution::find($institutionId);

        // Get the course
        $course = Course::find($courseId);


        // if sr_flag is set to 1 generate NSIN
        if ($studentRegistration->sr_flag == 1) {

            $nsinMonth = Str::upper(Str::limit($month, 3));
            $nsinYear = Str::substr($year->year, 2);
            $nsinInstituteCode = $institution->code;
            $nsinCourseCode = $course->course_code;

            // Generate the NSIN
            $nsin = $nsinMonth . '' . $nsinYear . '/' . $nsinInstituteCode . '/' . $nsinCourseCode . '/' . $studentId;

            $studentRegistration->NSIN = $nsin;
            $studentRegistration->save();
        }
    }

    /**
     * Handle the StudentRegistration "deleted" event.
     */
    public function deleted(StudentRegistration $studentRegistration): void
    {
        //
    }

    /**
     * Handle the StudentRegistration "restored" event.
     */
    public function restored(StudentRegistration $studentRegistration): void
    {
        //
    }

    /**
     * Handle the StudentRegistration "force deleted" event.
     */
    public function forceDeleted(StudentRegistration $studentRegistration): void
    {
        //
    }
}
