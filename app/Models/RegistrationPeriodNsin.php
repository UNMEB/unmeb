<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class RegistrationPeriodNsin extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $fillable = [
        'id',
        'year_id',
        'month',
        'flag'
    ];

    public function year()
    {
        return $this->belongsTo(Year::class);
    }
}
