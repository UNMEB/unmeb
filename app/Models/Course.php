<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    use HasFactory;

    public function surchargeFees()
    {
        return $this->hasMany(SurchargeFee::class, 'course_id');
    }

    // Define inverse relationship
    public function registrations()
    {
        return $this->hasMany(Registration::class, 'course_id');
    }
}