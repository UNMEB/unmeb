<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Transaction extends Model
{
    use HasFactory, AsSource, Filterable;

    protected $fillable = [
        'amount',
        'type',
        'status',
        'account_id',
        'approved_by',
        'institution_id',
        'deposited_by',
        'remote_transaction_id',
        'initiated_by',
        'user_id',
        'comment',
        'method',
        'locked'
    ];

    protected $allowedFilters = [
        'institution_id' => Where::class,
        'transaction_type' => Where::class,
        'transaction_method' => Where::class,
        'remote_transaction_id' => Where::class,
        'status' => Where::class,
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function initiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiated_by');
    }

    public function getAuthUser(): ?User
    {
        return auth()->user();
    }

    public function logs()
    {
        return $this->hasMany(TransactionLog::class);
    }

    public function meta()
    {
        return $this->hasOne(TransactionMeta::class);
    }

    public function institution()
    {
        return $this->belongsTo(Institution::class);
    }

    public function getFullCommentAttribute()
    {
        $studentId = Str::after($this->comment, 'STUDENT ID: ');
        $student = Student::find($studentId);
        if ($student) {
            return $this->comment . ' ' . $student->full_name;
        } else {
            return $this->comment;
        }
    }
}
