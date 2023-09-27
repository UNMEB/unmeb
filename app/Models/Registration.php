<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Registration extends Model
{
    use HasFactory, AsSource;


    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

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
        return $this->belongsTo(Surcharge::class, 'surcharge_id');
    }

    public function surchargeFee()
    {
        return $this->hasOne(SurchargeFee::class, 'surcharge_id', 'surcharge_id')
            ->join('courses', 'surcharge_fee.course_id', '=', 'courses.course_id');
    }

    public function year()
    {
        return $this->belongsTo(Year::class); // Replace Year::class with the actual class name you want to associate.
    }
}
