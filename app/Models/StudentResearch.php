<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class StudentResearch extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $fillable = [
        'student_id',
        'research_abstract',
        'research_title',
        'year',
        'submission_date'
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
