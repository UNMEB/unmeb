<?php

namespace App\Models;

use App\Orchid\Filters\InstitutionIdFilter;
use App\Traits\HasInstitution;
use App\Traits\OrderByLatest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Where;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class NsinRegistration extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, HasInstitution, OrderByLatest;

    protected $allowedFilters = [
        'institution_id' => Where::class,
        'course_id' => Where::class,
        'month' => Where::class,
        'year_id' => Where::class,
    ];

    public function nsinStudentRegistrations(): HasMany
    {
        return $this->hasMany(NsinStudentRegistration::class);
    }
}
