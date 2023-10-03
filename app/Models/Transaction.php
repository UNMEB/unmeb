<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Transaction extends Model
{
    use HasFactory, AsSource;

    protected $fillable = [
        'amount',
        'method',
        'type',
        'account_id',
        'is_approved',
        'institution_id',
        'deposited_by'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
