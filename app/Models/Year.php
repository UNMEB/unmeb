<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Year extends Model
{
    use HasFactory, AsSource;

    protected $filable = [
        'id',
        'name',
        'is_active'
    ];

    public function studentRegistrations()
    {
        return $this->hasMany(StudentRegistration::class);
    }
}
