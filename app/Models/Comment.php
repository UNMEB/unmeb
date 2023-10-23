<?php

namespace App\Models;

use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Comment extends Model
{
    use HasFactory, AsSource, Filterable, HasInstitution;

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
