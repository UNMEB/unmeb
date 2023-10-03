<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class StudentRegistration extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'institution_id',
        'course_id',
        'student_id',
        'month',
        'year_id',
        'is_completed',
        'is_approved',
        'is_book',
        'is_verified',
        'remarks',
    ];


    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function year()
    {
        return $this->belongsTo(Year::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Assuming you have access to the current user and role as arguments
        $currentUser = auth()->user();
        $isSystemAdmin = $currentUser->inRole('system-admin');

        static::addGlobalScope('institution', function (Builder $builder) use ($currentUser, $isSystemAdmin) {
            $institutionId = $currentUser->institution_id;

            if (!$isSystemAdmin) {
            $builder->where('institution_id', $institutionId);
            }
        });
    }
    public function currentUser(): User
    {
        return auth()->user();
    }
}
