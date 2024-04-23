<?php

namespace App\Models;

use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class StudentRegistration extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, LogsActivity;

    protected $fillable = [
        'student_id',
        'registration_id',
        'trial',
        'course_codes',
        'no_of_papers',
        'sr_flag',
        'remarks'
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
