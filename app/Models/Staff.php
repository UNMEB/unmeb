<?php

namespace App\Models;

use App\Models\Scopes\InstitutionScope;
use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Staff extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, HasInstitution, Attachable, LogsActivity;

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
        'picture'
    ];

    protected static function booted()
    {
        $user = auth()->user() ?? null;
        static::addGlobalScope(new InstitutionScope($user));
    }

    public function getAvatarAttribute()
    {
        // Check if there is a picture and image exists in public path
        if ($this->picture) {
            // Return img tag
            return '<img src="' . $this->picture . '" width="50px">';
        }

        // Return placeholder avatar
        return '<img src="' . asset('placeholder/avatar.png') . '" width="50px">';
    }

    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
