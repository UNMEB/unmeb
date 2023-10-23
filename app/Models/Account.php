<?php

namespace App\Models;

use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Orchid\Filters\Filterable;
use Orchid\Platform\Concerns\Sortable;
use Orchid\Screen\AsSource;

class Account extends Model
{
    use HasFactory, AsSource, Filterable, Sortable, HasInstitution;


    protected $fillable = [
        'institution_id',
        'balance'
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
            ->where('is_approved', false)
            ->sum('amount');

        if ($totalPendingDebits > 0) {
            // Calculate the future balance
            $futureBalance = $currentBalance + $totalPendingDebits;

            return $futureBalance;
        }

        return 0;
    }
}
