<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class LogbookFee extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'course_id',
        'course_fee'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

}
