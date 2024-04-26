<?php

namespace App\Models;

use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Where;
use Orchid\Screen\AsSource;

class Transaction extends Model
{
    use HasFactory, AsSource, HasInstitution, Filterable;

    protected $fillable = [
        'amount',
        'method',
        'type',
        'account_id',
        'is_approved',
        'institution_id',
        'deposited_by',
        'comment',
        'remote_transaction_id',
        'status'
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

    protected static function booted()
    {
        // Add a global scope to filter transactions based on user's institution access
        static::addGlobalScope('institutionAccess', function (Builder $builder) {
            $user = auth()->user();

            if ($user && $user->hasAccess('platform.internals.all_institutions')) {
                // User has access to all institutions, no need to filter
                return;
            }

            // Use the user's institution ID to filter transactions
            $builder->whereHas('account', function ($query) use ($user) {
                if ($user->institution) {
                    $query->where('institution_id', $user->institution->id);
                }
            });
        });
    }
}
