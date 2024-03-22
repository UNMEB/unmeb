<?php

namespace App\Models;

use App\Models\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\WhereBetween;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RegistrationReport extends Model
{
    use HasFactory, AsSource, Filterable, LogsActivity;

    protected $fillable = [
        'id',
        'course_id',
        'paper_id',
        'registration_id',
        'registration_period_id',
        'institution_id',
        'attempt',
        'semester',
        'year',
        'total',
    ];

    // Allowed Filters
    protected $allowedFilters = [
        'institution_id' => Where::class,
        'course_id' => Where::class,
        'paper_id' => Where::class,
        'registration_id' => Where::class,
        'registration_period_id' => Where::class,
        'attempt' => Where::class,
        'semester' => Where::class,
        'year' => WhereBetween::class,
        'total' => WhereBetween::class,
    ];

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function paper(): BelongsTo
    {
        return $this->belongsTo(Paper::class);
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class);
    }

    public function registrationPeriod(): BelongsTo
    {
        return $this->belongsTo(RegistrationPeriod::class);
    }

    public function institution(): BelongsTo
    {
        return $this->belongsTo(Institution::class);
    }

    protected static function booted()
    {
        $user = auth()->user() ?? null;
        static::addGlobalScope(new InstitutionScope($user));
    }

    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
