<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class NsinRegistrationPeriod extends Model
{
    use HasFactory, AsSource, Filterable, Sortable;

    protected $fillabe = [];

    public function year()
    {
        return $this->belongsTo(Year::class);
    }
}
