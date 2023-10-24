<?php 

// app/Traits/OrderByLatest.php

namespace App\Traits;

trait OrderByLatest
{
    public function scopeLatest($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
