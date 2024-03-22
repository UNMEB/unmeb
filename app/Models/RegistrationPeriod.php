<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class RegistrationPeriod extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, LogsActivity;

    public function getStartAndEndDateAttribute()
    {
        return $this->reg_start_date . " - " . $this->reg_end_date;
    }

    protected $fillable = [
        'reg_start_date',
        'reg_end_date',
        'academic_year',
        'flag'
    ];

    public $timestamps = false;

    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
