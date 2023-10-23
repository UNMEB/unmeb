<?php

namespace App\Models\Scopes;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class InstitutionScope implements Scope
{
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        // Check if the user is not null
        if ($this->user) {
            $hasAccess = $this->user->hasAccess('platform.internals.all_institutions');

            // Get the institution id
            $institutionId = $this->user->institution_id;

            // Check if the user has access to the institution
            if (!$hasAccess) {
                $builder->where('institution_id', $institutionId);
            }
        }
    }
}
