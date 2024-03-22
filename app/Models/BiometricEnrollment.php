<?php

namespace App\Models;

use App\Models\Scopes\InstitutionScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class BiometricEnrollment extends Model
{
    use HasFactory, LogsActivity;

    protected static function booted()
    {
        $user = auth()->user() ?? null;
        static::addGlobalScope(new InstitutionScope($user));
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
