<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class TicketPriority extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'name',
        'color'
    ];

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }
}
