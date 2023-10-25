<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\BaseHttpEloquentFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class StudentNameFilter extends BaseHttpEloquentFilter
{
    public function run(Builder $builder): Builder
    {
        // Split the name and check combinations of surname, othername, firstname

        return $builder->where('surname', 'like', '%' . $this->getHttpValue() . '%')
            ->orWhere('firstname', 'like', '%' . $this->getHttpValue() . '%')
            ->orwhere('othername', 'like', '%' . $this->getHttpValue() . '%');
    }
}
