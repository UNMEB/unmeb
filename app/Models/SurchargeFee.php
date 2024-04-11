<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class SurchargeFee extends Model
{
    use HasFactory, AsSource, Filterable, Sortable;

    protected $fillable = [
        'course_fee',
        'course_id'
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
