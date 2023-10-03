<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class StudentRegistrationPeriod extends Model
{
    use HasFactory, AsSource;

    public function year()
    {
        return $this->belongsTo(Year::class);
    }
}
