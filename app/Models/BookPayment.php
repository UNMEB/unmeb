<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class BookPayment extends Model
{
    use HasFactory, AsSource;

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
