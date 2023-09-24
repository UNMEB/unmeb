<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class SurchargeFee extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $fillable = [
        'id',
        'surcharge_id',
        'course_id',
        'fee'
    ];

    public function surcharge()
    {
        return $this->belongsTo(Surcharge::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
