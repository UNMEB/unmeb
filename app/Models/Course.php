<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Course extends Model
{
    use HasFactory, AsSource;

    public function institutions()
    {
        return $this->belongsToMany(Institution::class, 'institution_course', 'course_id', 'institution_id');
    }

    public function papers()
    {
        return $this->belongsToMany(Paper::class, 'course_paper', 'course_id', 'paper_id');
    }

    public function surchargeFees()
    {
        return $this->hasMany(SurchargeFee::class, 'course_id');
    }


}
