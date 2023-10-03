<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

use Illuminate\Database\Eloquent\Builder;

class Student extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $fillable = [
        'id',
        'surname',
        'othername',
        'firstname',
        'dob',
        'district_id',
        'gender',
        'country',
        'address',
        'nsin',
        'telephone',
        'email',
        'old',
        'registration_date',
        'passport'
    ];

    public function papers()
    {
        return $this->belongsToMany(Paper::class, 'course_student_paper', 'student_id', 'paper_id')->withTimestamps();
    }

    // Define a getter for the full name
    public function getFullNameAttribute()
    {
        // You can customize the format of the full name based on your requirements
        return "{$this->firstname} {$this->surname} {$this->othername}";
    }

    public function district()
    {
        return $this->belongsTo(District::class);
    }

    public function registrations()
    {
        return $this->hasMany(StudentRegistration::class);
    }

    public function examRegistrations()
    {
        return $this->hasMany(ExamRegistration::class);
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

            return $query->whereHas('registrations', function ($query) use ($institution) {
                $query->where('institution_id', $institution->id);
            });
        }
    }




    /**
     * @param Builder $query
     *
     * @return Builder
     */
    public function scopeForSelectedCourse(Builder $query, $institutionId)
    {
        $institution = Institution::find($institutionId);

        return $query->whereHas('registrations', function ($query) use ($institution) {
            $query->where('institution_id', $institution->id);
        });
    }

    // /**
    //  * The "booted" method of the model.
    //  *
    //  * @return void
    //  */
    // protected static function boot()
    // {
    //     parent::boot();

    //     static::addGlobalScope('institution', function (Builder $builder) {
    //         $user = auth()->user(); // Assuming you have user authentication set up.

    //         // Apply the scope only if the user is authenticated and has an institution ID.
    //         if ($user && $user->institution_id) {
    //             $builder->whereHas('registrations', function ($query) use ($user) {
    //                 $query->where('institution_id', $user->institution_id);
    //             });
    //         }
    //     });
    // }

    public function currentUser(): User
    {
        return auth()->user();
    }
}
