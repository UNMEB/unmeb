<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstitutionCourse extends Model
{
    use HasFactory;

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    // Define a many-to-many relationship with the Course model
    public function courses()
    {
        return $this->belongsToMany(Course::class);
    }

}
