<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'nsin_registration_id',
        'number_of_students',
        'total',
        'receipt',
        'ready',
        'approved',
        'date_submitted'
    ];
}
