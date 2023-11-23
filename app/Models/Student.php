<?php

namespace App\Models;

use App\Models\Scopes\InstitutionScope;
use App\Orchid\Filters\StudentNameFilter;
use App\Traits\OrderByLatest;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
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

    protected $allowedFilters = [
        'gender' => Like::class,
        'district_id' => Where::class,
        'name' => StudentNameFilter::class
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

    public function getAvatar2Attribute()
    {
        // Check if there is a passport and image exists in public path
        if ($this->passport && file_exists(public_path('photos/' . $this->passport))) {
            // Return img tag
            return '<img src="' . asset('photos/' . $this->passport) .  '" style="width: 180px; border-radius: 10px;">';
        }

        // Return placeholder avatar
        return '<img src="' . asset('placeholder/avatar.png') . '" style="width: 180px; border-radius: 10px;">';
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

    public function institution()
    {
        return $this->belongsTo(Institution::class);
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

    public function getStudentWithNinAttribute()
    {
        return "{$this->firstname} - {$this->surname} - ({$this->nin})";
    }

    protected static function booted()
    {
        $user = auth()->user() ?? null;
        static::addGlobalScope(new InstitutionScope($user));
    }

    public function scopeFilterByInstitution($query, $data)
    {
        return $query->where('institution_id', $data['id']);
    }
}
