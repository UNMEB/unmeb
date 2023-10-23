<?php

namespace App\Models;

use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NsinStudentRegistration extends Model
{
    use HasFactory;

    public function nsinRegistration(): BelongsTo
    {
        return $this->belongsTo(NsinRegistration::class);
    }
}
