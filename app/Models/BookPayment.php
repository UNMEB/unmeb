<?php

namespace App\Models;

use App\Traits\HasInstitution;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookPayment extends Model
{
    use HasFactory, HasInstitution;

    protected $fillable = [
        
    ];
}
