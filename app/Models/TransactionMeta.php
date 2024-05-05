<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionMeta extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'type',
        'registration_id',
        'student_registration_id'
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
