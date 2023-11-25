<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Orchid\Filters\Filterable;
use Orchid\Screen\AsSource;

class StudentPaperRegistration extends Model
{
    use HasFactory, Filterable, AsSource;

    protected $table = 'student_paper_registration';

    public $timestamps = false;
}
