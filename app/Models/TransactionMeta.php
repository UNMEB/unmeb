<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionMeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'json',
    ];


    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
