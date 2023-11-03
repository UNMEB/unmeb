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
