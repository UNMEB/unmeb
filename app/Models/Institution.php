<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Institution extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $allowedFilters = [
        'institution_name' => Like::class,
        'institution_type' => Like::class,
        'institution_location' => Like::class,
        'code' => Like::class,
        'phone_no' => Like::class,
    ];

    protected $fillable = [
        'short_name',
        'institution_name',
        'institution_type',
        'category',
        'institution_location',
        'code',
        'email',
        'phone_no',
        'box_no'
    ];

    public function courses()
    {
        return $this->belongsToMany(Course::class, 'institution_course', 'institution_id', 'course_id')
        ->withPivot('flag');
    }

    public function staff()
    {
        return $this->hasMany(Staff::class, 'institution_id');
    }

    public function account()
    {
        return $this->hasOne(Account::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function currentUser(): User
    {
        return auth()->user();
    }

    public function scopeUserInstitutions($query)
    {
        // Get the authenticated user
        $user = auth()->user();

        // Check if the user has access to 'platform.internals.all_institutions'
        $hasAccess = $user->hasAccess('platform.internals.all_institutions');

        // Apply a condition to limit institutions to those associated with the user
        if ($user && !$hasAccess) {
            // If the user doesn't have access to all institutions, check for institution_id
            if (!is_null($user->institution_id)) {
                return $query->where('id', $user->institution_id);
            }
        }

        return $query;
    }



}
