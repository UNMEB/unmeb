<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Attachment\Attachable;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class Staff extends Model
{
    use HasFactory, AsSource, Filterable, Attachable;

    protected $fillable = [
        'institution_id',
        'name',
        'designation',
        'status',
        'education',
        'qualification',
        'council',
        'reg_no',
        'reg_date',
        'lic_exp',
        'experience',
        'telephone',
        'email',
        'bank',
        'branch',
        'acc_no',
        'acc_name',
        'receipt'
    ];

    public function institution()
    {
        return $this->belongsTo(Institution::class, 'institution_id');
    }
}
