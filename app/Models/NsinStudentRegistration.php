<?php

namespace App\Models;

use App\Traits\HasInstitution;
use App\Traits\OrderByLatest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NsinStudentRegistration extends Model
{
    use HasFactory, OrderByLatest, LogsActivity;

    protected $fillable = [
        'verify',
        'remarks',
    ];

    public function nsinRegistration(): BelongsTo
    {
        return $this->belongsTo(NsinRegistration::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    // Temporary property, not saved in the database
    public $is_observer_triggered = false;

    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
