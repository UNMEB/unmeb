<?php

namespace App\Observers;

use App\Models\NsinStudentRegistration;

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
        //
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
