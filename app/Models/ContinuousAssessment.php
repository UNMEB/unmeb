<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class ContinuousAssessment extends Model
{
    use HasFactory, AsSource, Filterable, Sortable;

    protected $fillable = [
        'registration_period_id',
        'institution_id',
        'course_id',
        'paper_id',
        'student_id',
        'theory_marks',
        'practical_marks',
        'total_marks',
        'created_by',
    ];

    protected $casts = [
        'theory_marks' => 'array',
        'practical_marks' => 'array',
    ];

    public $timestamps = true;

    // Define conversion factors as constants
    // const PRACTICAL_TEST_CONVERSION_FACTOR = 0.10;
    // const CLINICAL_PRACTICE_ASSESSMENT_CONVERSION_FACTOR = 0.10;
    // const LOGBOOK_ASSESSMENT_CONVERSION_FACTOR = 0.20;
    // const ASSIGNMENT_CONVERSION_FACTOR = 0.20; // Assuming 20% weightage for assignment
    // const TEST_CONVERSION_FACTOR = 0.20;       // Assuming 20% weightage for test

    public function registrationPeriod()
    {
        return $this->belongsTo(RegistrationPeriod::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function paper()
    {
        return $this->belongsTo(Paper::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getTotalTheoryMarkAttribute()
    {
        return is_array($this->theory_marks) ? array_sum($this->theory_marks) : 0;
    }

    public function getTotalPracticalMarkAttribute()
    {
        return is_array($this->practical_marks) ? array_sum($this->practical_marks) : 0;
    }



}
