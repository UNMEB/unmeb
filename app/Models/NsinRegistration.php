<?php

namespace App\Models;

use App\Traits\HasInstitution;
use App\Traits\OrderByLatest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class NsinRegistration extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, HasInstitution, OrderByLatest;

    public function nsinStudentRegistrations(): HasMany
    {
        return $this->hasMany(NsinStudentRegistration::class);
    }
}
