<?php

namespace App\Models;

use App\Traits\OrderByLatest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NsinStudentRegistration extends Model
{
    use HasFactory, OrderByLatest, LogsActivity, Filterable, AsSource;

    protected $fillable = [
        'student_code',
        'nsin',
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
