<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class NsinRegistrationPeriod extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, LogsActivity;

    protected $fillabe = [
        'year_id'
    ];

    public $timestamps = false;

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

    public function registrations()
    {
        return $this->hasMany(NsinRegistration::class, 'month', 'month')
                    ->where('year_id', $this->year_id);
    }
}
