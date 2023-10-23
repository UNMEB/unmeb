<?php

namespace App\Models;

use App\Models\Scopes\InstitutionScope;
use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class Staff extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, HasInstitution;

    protected $fillable = [
        'institution_id',
        'staff_name',
        'designation',
        'status',
        'education',
        'qualification',
        'council',
        'reg_no',
        'lic_exp',
        'telephone',
        'email',
        'bank',
        'branch',
        'acc_no',
        'acc_name',
        'receipt',
    ];

    protected static function booted()
    {
        $user = auth()->user() ?? null;
        static::addGlobalScope(new InstitutionScope($user));
    }
}
