<?php

namespace App\Models;

use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Screen\AsSource;

class Transaction extends Model
{
    use HasFactory, AsSource, HasInstitution;

    protected $fillable = [
        'amount',
        'method',
        'type',
        'account_id',
        'is_approved',
        'institution_id',
        'deposited_by',
        'comment'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function initiatedBy()
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function getAuthUser(): ?User
    {
        return auth()->user();
    }
}
