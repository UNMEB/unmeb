<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class StudentRegistrationNsin extends Model
{
    use HasFactory, AsSource;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
