<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CaseRecordBook extends Model
{
    use HasFactory;

    protected $fillable = [
        'course_id',
        'case_fee'
    ];
}
