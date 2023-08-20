<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Surcharge extends Model
{
    use HasFactory;

    public function surchargeFees()
    {
        return $this->hasMany(SurchargeFee::class, 'surchage_id');
    }
}