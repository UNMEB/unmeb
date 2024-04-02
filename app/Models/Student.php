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
use Illuminate\Http\Request;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Like;
use Orchid\Filters\Types\Where;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Log;
use Spatie\Activitylog\Contracts\Activity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;



class Student extends Model
{
    use HasFactory, AsSource, Filterable, Attachable, Sortable, OrderByLatest, LogsActivity;

    // Add this property to store the request instance
    protected $request;

    protected $fillable = [
        'surname',
        'firstname',
        'othername',
        'passport',
        'gender',
        'dob',
        'district_id',
        'country_id',
        'location',
        'NSIN',
        'telephone',
        'email',
        'old',
        'date_time',
        'nin',
        'lin',
        'passport_number',
        'applied_program',
        'institution_id',
    ];

    protected $casts = [
        'dob' => 'datetime',
    ];

    protected $allowedFilters = [
        'students.institution_id' => Where::class,
        'gender' => Like::class,
        'district_id' => Where::class,
        'name' => StudentNameFilter::class,

    ];

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function getFullNameAttribute()
    {
        return $this->surname . ' ' . $this->firstname . ' ' . $this->othername;
    }

    // Accessor to determine the identifier
    public function getIdentifierAttribute()
    {
        // Check if all identifiers are provided
        if ($this->nin && $this->lin && $this->passport_number) {
            return $this->nin; // If all are provided, prioritize nin
        }

        // If not all are provided, prioritize according to availability
        if ($this->nin) {
            return $this->nin;
        } elseif ($this->lin) {
            return $this->lin;
        } elseif ($this->passport_number) {
            return $this->passport_number;
        }

        // If none is provided, return null or any default value you prefer
        return null;
    }

    public function getAvatarAttribute()
    {
        // Check if there is a passport and image exists in public path
        if ($this->passport) {
            // Return img tag
            return '<img src="' . $this->passport . '" width="50px">';
        }

        // Return placeholder avatar
        return '<img src="' . asset('placeholder/avatar.png') . '" width="50px">';
    }

    public function getAvatar2Attribute()
    {
        // Check if there is a passport and image exists in public path
        if ($this->passport) {
            // Return img tag
            return '<img src="' . $this->passport . '" style="width: 180px; border-radius: 10px;">';
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

    public function currentUser(): ?User
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

    public function getLatestNinAttribute()
    {
        $record = $this->nsinRegistrations()->latest()->first();

        Log::info('Student Record', $record->toArray());

        return $record ? $record->getNin() : "No NSIN";
    }

    public function tapActivity(Activity $activity, string $eventName)
    {
        $activity->description = "activity.logs.message.{$eventName}";
        $activity->ip_address = request()->ip();
    }

    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->dontSubmitEmptyLogs();
    }

    public function researches()
    {
        return $this->hasMany(StudentResearch::class, 'student_id');
    }
}
