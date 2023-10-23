<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class Surcharge extends Model
{
    use HasFactory, AsSource, Filterable, Sortable;

    public function surchargeFees()
    {
        return $this->hasMany(SurchargeFee::class);
    }
}
