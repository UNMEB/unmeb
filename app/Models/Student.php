<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\FileHelpers;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Student extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $fillable = [
        'nsin',
        'surname',
        'firstname',
        'othername',
        'gender',
        'dob',
        'district_id',
        'telephone',
        'email',
        'old_student'
    ];

    public function practicalAssessmentMarks()
    {
        return $this->hasMany(PracticalAssessmentMark::class);
    }

    public function theoryAssessmentMarks()
    {
        return $this->hasMany(TheoryAssessmentMark::class);
    }

    public function studentPaperContributions()
    {
        return $this->hasMany(StudentPaperContribution::class);
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
}
