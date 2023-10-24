<?php

namespace App\Models;

use App\Traits\OrderByLatest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class Student extends Model
{
    use HasFactory, AsSource, Filterable, Attachable, Sortable, OrderByLatest;

    protected $fillable = [
        'surname',
        'firstname',
        'othername',
        'passport',
        'gender',
        'dob',
        'district_id',
        'country',
        'location',
        'NSIN',
        'telephone',
        'email',
        'old',
        'date_time'
    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function getFullNameAttribute()
    {
        return $this->surname . ' ' . $this->firstname . ' ' . $this->othername;
    }

    public function getAvatarAttribute()
    {
        // Check if there is a passport and image exists in public path
        if ($this->passport && file_exists(public_path('photos/' . $this->passport))) {
            // Return img tag
            return '<img src="' . asset('photos/' . $this->passport) .  '" width="50px">';
        }

        // Return placeholder avatar
        return '<img src="' . asset('placeholder/avatar.png') . '" width="50px">';
    }

    public function nsinStudentRegistrations(): HasMany
    {
        return $this->hasMany(NsinStudentRegistration::class);
    }


    public function nsinRegistrations()
    {
        return $this->hasMany(NsinRegistration::class);
    }

    public function examRegistrations()
    {
        return $this->hasMany(Registration::class);
    }

    public function currentUser(): User
    {
        return auth()->user();
    }

    /**
     * Accessor for the "studentWithNsin" attribute.
     *
     * @return string|null
     */
    public function getStudentWithNsinAttribute()
    {
        return "{$this->firstname} - {$this->surname} - ({$this->nsin})";
    }

    protected static function booted()
    {
        // Add a global scope to filter students based on user's institution access
        static::addGlobalScope('institutionAccess', function (Builder $builder) {
            $user = auth()->user();

            if ($user && $user->hasAccess('platform.internals.all_institutions')) {
                // User has access to all institutions, no need to filter
                return;
            }

            // Use the user's institution ID to filter students
            $builder->whereHas('nsinStudentRegistrations.nsinRegistration', function ($query) use ($user) {
                if ($user->institution) {
                    $query->where('institution_id', $user->institution->id);
                }
            });
        });
    }



}
