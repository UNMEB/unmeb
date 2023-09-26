<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Course extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $fillable = [
        'id',
        'name',
        'code',
        'duration'
    ];

    public function registrations()
    {
        return $this->hasMany(Registration::class, 'course_id');
    }

    public function nsinRegistrations()
    {
        return $this->hasMany(NsinRegistration::class);
    }

    public function surchargeFees()
    {
        return $this->hasMany(SurchargeFee::class, 'course_id');
    }

    public function studentsRegistrationNsin()
    {
        return $this->hasManyThrough(StudentRegistrationNsin::class, Nsinregistration::class, 'course_id', 'nsinregistration_id');
    }

    public function papers()
    {
        return $this->belongsToMany(Paper::class, 'course_paper', 'course_id', 'paper_id')
        ->where('course_paper.flag', 1)
        ->where('papers.year_of_study', 'Year 1 semester 1');
    }
}
