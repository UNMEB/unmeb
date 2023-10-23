<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Course extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'course_name',
        'course_code',
        'duration'
    ];

    public function institutions()
    {
        return $this->belongsToMany(Institution::class, 'institution_course', 'course_id', 'institution_id');
    }

    public function papers()
    {
        return $this->belongsToMany(Paper::class, 'course_paper', 'course_id', 'paper_id')
        ->withPivot('flag');
    }

    // Institution Courses
    public function scopeInstitutionCourses($query): Builder
    {
    }
}
