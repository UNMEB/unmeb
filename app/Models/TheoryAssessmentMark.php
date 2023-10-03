<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class TheoryAssessmentMark extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'institution_id',
        'course_id',
        'paper_id',
        'assignment_1',
        'assignment_2',
        'test_1',
        'test_2',
        'assignment_mark',
        'test_mark',
        'total_mark'
    ];

    public function institution()
    {
    }

    public function course()
    {
    }

    public function paper()
    {
    }

    public function student()
    {
    }
}
