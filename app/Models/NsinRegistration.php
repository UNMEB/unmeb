<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class NsinRegistration extends Model
{
    use HasFactory, AsSource, Filterable;

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
        'old'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    public function registrationPeriodNsin()
    {
        return $this->belongsTo(RegistrationPeriodNsin::class);
    }

    public function studentsRegistrationNsin()
    {
        return $this->hasMany(StudentRegistrationNsin::class);
    }


}
