<?php

namespace App\Models;

use App\Orchid\Filters\InstitutionIdFilter;
use App\Traits\HasInstitution;
use App\Traits\OrderByLatest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Where;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NsinRegistration extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, OrderByLatest, LogsActivity;

    protected $fillable = [
        'institution_id',
        'course_id',
        'amount',
        'receipt',
        'month',
        'year_id',
        'completed',
        'approved',
        'books',
        'nsin',
        'nsin_verify',
        'old',
        'date_time',
    ];

    protected $allowedFilters = [
        'institution_id' => Where::class,
        'course_id' => Where::class,
        'month' => Where::class,
        'year_id' => Where::class,
    ];

    public function nsinStudentRegistrations(): HasMany
    {
        return $this->hasMany(NsinStudentRegistration::class);
    }

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }
}
