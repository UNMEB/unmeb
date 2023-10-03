<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Paper extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'id',
        'name',
        'study_period',
        'abbrev',
        'code',
        'paper'
    ];
}
