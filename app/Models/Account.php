<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Filters\Types\Where;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Account extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, LogsActivity;

    protected $fillable = [
        'institution_id',
        'balance'
    ];

    protected $allowedFilters = [
        'institution_id' => Where::class,
    ];

    public function lastTransaction()
    {
        return Transaction::where('account_id', $this->id)->latest()->first();
    }

    public function getFutureBalanceAttribute()
    {
        $currentBalance = $this->balance;

        // Calculate the total amount of pending debit transactions
        $totalPendingDebits = Transaction::where('account_id', $this->id)
            ->where('type', 'credit')
            ->where('status', 'pending')
            ->sum('amount');

        if ($totalPendingDebits > 0) {
            // Calculate the future balance
            $futureBalance = $currentBalance + $totalPendingDebits;

            return $futureBalance;
        }

        return 0;
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    /**
     * @return \Spatie\Activitylog\LogOptions
     */
    function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults();
    }
}
