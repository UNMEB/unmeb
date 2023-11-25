<?php

namespace App\Observers;

use App\Models\Course;
use App\Models\Institution;
use App\Models\NsinRegistration;
use App\Models\NsinStudentRegistration;
use App\Models\Student;
use App\Models\StudentPaperRegistration;
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

        // Check if the observer should proceed
        if ($nsinStudentRegistration->is_observer_triggered) {
            return;
        }

        // Set the flag to true to prevent recursive calls
        $nsinStudentRegistration->is_observer_triggered = true;

        // Eager load the nsinRegistration with necessary fields, including the related year
        $nsinRegistration = $nsinStudentRegistration->load([
            'nsinRegistration' => function ($query) {
                $query->select('id', 'month', 'year_id', 'institution_id', 'course_id')
                    ->with('year:id,year'); // Assuming 'year' is the relationship name and 'year' is the field in the years table
            }
        ])->nsinRegistration;

        // if verify is set to 1 generate NSIN
        if ($nsinStudentRegistration->verify == 1) {
            // Use eager loaded relations and select only necessary fields
            $institutionCode = Institution::where('id', $nsinRegistration->institution_id)->value('code');
            $courseCode = Course::where('id', $nsinRegistration->course_id)->value('course_code');

            $nsinMonth = Str::upper(Str::limit($nsinRegistration->month, 3, ''));
            $nsinYear = Str::substr($nsinRegistration->year->year, 2); // Accessing year from the eager loaded relationship

            // Generate the NSIN using interpolation
            $nsin = "{$nsinMonth}{$nsinYear}/{$institutionCode}/{$courseCode}/{$nsinStudentRegistration->student_id}";

            $nsinStudentRegistration->nsin = $nsin;
            $nsinStudentRegistration->saveQuietly();

            Student::find($nsinStudentRegistration->student_id)->update([
                'nsin' => $nsin
            ]);

            // Reset the flag
            $nsinStudentRegistration->is_observer_triggered = false;
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
