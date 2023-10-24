<?php

namespace App\Models;

use App\Traits\HasInstitution;
use App\Traits\OrderByLatest;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NsinStudentRegistration extends Model
{
    use HasFactory, OrderByLatest;

    protected $fillable = [
        'verify',
        'remarks',
    ];

    public function nsinRegistration(): BelongsTo
    {
        return $this->belongsTo(NsinRegistration::class);
    }
}
