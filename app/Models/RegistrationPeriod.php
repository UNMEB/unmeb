<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class RegistrationPeriod extends Model
{
    use HasFactory, AsSource, Filterable, Sortable;

    public function getStartAndEndDateAttribute()
    {
        return $this->reg_start_date . " - ". $this->reg_end_date;
    }
}
