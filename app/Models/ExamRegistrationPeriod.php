<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class ExamRegistrationPeriod extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'id',
        'start_date',
        'end_date',
        'academic_year',
        'is_active'
    ];
}
