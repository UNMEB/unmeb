<?php

namespace App\Models;

use App\Traits\OrderByLatest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Year extends Model
{
    use HasFactory, AsSource, OrderByLatest;

    protected $fillable = [
        'year'
    ];
}
