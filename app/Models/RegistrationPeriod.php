<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class RegistrationPeriod extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $fillable = [
        'id',
        'start_date',
        'end_date',
        'academic_year'
    ];
}
