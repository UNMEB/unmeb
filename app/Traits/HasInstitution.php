<?php

namespace App\Traits;

use App\Models\Institution;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

trait HasInstitution
{

    /**
     * Define the institution relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public static function bootHasInstitution()
    {
        static::addGlobalScope(function ($query) {
        });
    }
}
