<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class BiometricAccessLog extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'institution_id',
        'course_id',
        'paper_id',
        'verification_date'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }
}
