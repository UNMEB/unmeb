<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NsinRegistration extends Model
{
    use HasFactory;

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
}
