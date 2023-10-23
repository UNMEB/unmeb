<?php

namespace App\Orchid\Layouts\Selection;

use App\Orchid\Filters\Filters\InstitutionNameFilter;
use Orchid\Filters\Filter;
use Orchid\Screen\Layouts\Selection;

class InstitutionFilters extends Selection
{
    /**
     * @return Filter[]
     */
    public function filters(): iterable
    {
        return [
            InstitutionNameFilter::class
        ];
    }
}
