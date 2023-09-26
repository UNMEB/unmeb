<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Comment extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = ['user_id', 'comment', 'email', 'date_submitted'];

    public function scopeDateSubmittedAfter(Builder $query, $date)
    {
        return $query->where('date_submitted', '>=', $date);
    }
}
