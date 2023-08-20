<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SurchargeFee extends Model
{
    use HasFactory;

    public function surcharge()
    {
        return $this->belongsTo(Surcharge::class, 'surcharge_id');
    }

    public function course()
    {
        return $this->belongsTo(Course::class, 'course_id');
    }
}