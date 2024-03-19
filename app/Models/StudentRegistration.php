<?php

namespace App\Models;

use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class StudentRegistration extends Model
{
    use HasFactory, AsSource, Filterable, Sortable;

    public function student()
    {
        return $this->belongsTo(Student::class);
    }
}
