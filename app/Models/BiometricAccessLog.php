<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BiometricAccessLog extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, LogsActivity;
    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
