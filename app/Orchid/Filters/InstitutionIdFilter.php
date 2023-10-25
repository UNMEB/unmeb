<?php

namespace App\Orchid\Filters;

use Illuminate\Database\Eloquent\Builder;
use Orchid\Filters\BaseHttpEloquentFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Field;

class InstitutionIdFilter extends BaseHttpEloquentFilter
{
    public function run(Builder $builder): Builder
    {
        return $builder->where('institutions.id', $this->getHttpValue());
    }
}
