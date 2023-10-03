<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Institution extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'name',
        'short_name',
        'location',
        'type',
        'code',
        'phone_no',
        'box_no'
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'institution_course', 'institution_id', 'course_id');
    }

    // Institution Account
    public function account()
    {
        return $this->hasOne(Account::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeForInstitution(Builder $query)
    {
        $institution = null;

        if (!$this->currentUser()->inRole('system-admin')) {
            $user = auth()->user();
            $institution = $user->institution;

            return $query->where('id', $institution->id);
        }
    }

    public function currentUser(): User
    {
        return auth()->user();
    }


    // protected static function boot()
    // {
    //     parent::boot();

    //     if (!auth()->user()->canAccess('platform.systems.administrator')) {
    //         static::addGlobalScope('institution', function (Builder $builder) {
    //             $institutionId = auth()->user()->institution_id;
    //             $builder->where('id', $institutionId);
    //         });
    //     }
    // }
}
