<?php

namespace App\Models;

use App\Models\Scopes\InstitutionScope;
use App\Orchid\Filters\Filters\InstitutionNameFilter;
use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Registration extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, LogsActivity;

    protected $allowedFilters = [
        'institution_name' => Like::class,
        'course_name' => Like::class,
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function registrationPeriod()
    {
        return $this->belongsTo(RegistrationPeriod::class);
    }

    public function surcharge()
    {
        return $this->belongsTo(Surcharge::class);
    }

    public function scopeIncomplete($query)
    {
        return $query->where('completed', 0);
    }

    // protected static function booted()
    // {
    //     $user = auth()->user() ?? null;
    //     static::addGlobalScope(new InstitutionScope($user));
    // }

    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
