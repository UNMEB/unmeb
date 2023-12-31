<?php

namespace App\Providers;

use App\Models\NsinStudentRegistration;
use App\Models\StudentRegistration;
use App\Models\Transaction;
use App\Observers\NsinStudentRegistrationObserver;
use App\Observers\TransactionObserver;
use App\Observers\StudentRegistrationObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Transaction::observe(TransactionObserver::class);
        // StudentRegistration::observe(StudentRegistrationObserver::class);
        NsinStudentRegistration::observe(NsinStudentRegistrationObserver::class);
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
