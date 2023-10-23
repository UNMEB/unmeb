<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class Paper extends Model
{
    use HasFactory, AsSource, Filterable, Sortable;

    protected $fillable = [
        'paper_name',
        'year_of_study',
        'paper',
        'abbrev',
        'code',
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'course_paper', 'paper_id', 'course_id')
            ->withPivot('flag');
    }
}
