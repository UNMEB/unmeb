<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Paper extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, LogsActivity;

    protected $fillable = [
        'paper_name',
        'year_of_study',
        'paper',
        'abbrev',
        'code',
    ];

    protected $allowedFilters = [
        'paper_name',
        'paper',
        'year_of_study'
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_paper', 'paper_id', 'course_id')
            ->withPivot('flag');
    }

    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
