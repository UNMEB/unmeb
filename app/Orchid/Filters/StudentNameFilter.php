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
        $searchTerm = $this->getHttpValue();
        $terms = explode(' ', $searchTerm);

        if (count($terms) == 2) {
            // Split the search term into two parts
            [$firstTerm, $secondTerm] = $terms;

            return $builder->where(function ($query) use ($firstTerm, $secondTerm) {
                // Check if the first term matches the firstname and the second term matches the surname
                $query->where('firstname', 'like', '%' . $firstTerm . '%')
                    ->where('surname', 'like', '%' . $secondTerm . '%');
            })->orWhere(function ($query) use ($firstTerm, $secondTerm) {
                // Check if the first term matches the surname and the second term matches the firstname
                $query->where('surname', 'like', '%' . $firstTerm . '%')
                    ->where('firstname', 'like', '%' . $secondTerm . '%');
            });
        }

        // If not two words, fall back to a more general search
        return $builder->where('firstname', 'like', '%' . $searchTerm . '%')
            ->orWhere('surname', 'like', '%' . $searchTerm . '%')
            ->orWhere('othername', 'like', '%' . $searchTerm . '%');
    }

}
