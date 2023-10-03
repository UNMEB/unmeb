<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class ExamRegistration extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'exam_registration_period_id',
        'institution_id',
        'course_id',
        'student_id',
        'is_active',
        'number_of_papers',
        'course_codes',
        'trial',
        'study_period',
        'is_completed',
        'is_approved',
        'is_verified',
        'remarks',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'number_of_papers' => 'integer',
        'is_completed' => 'boolean',
        'is_approved' => 'boolean',
        'is_verified' => 'boolean',
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

    public function examRegistrationPeriod()
    {
        return $this->belongsTo(ExamRegistrationPeriod::class);
    }


    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('institution', function (Builder $builder) {
            $institutionId = auth()->user()->institution_id;
            $builder->where('institution_id', $institutionId);
        });
    }


}
