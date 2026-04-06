<?php

namespace App\Models\Traits;

trait GroupProductivityScopes
{
    public function scopePositive($query)
    {
        return $query->where('value', '>', 0);
    }
    
    public function scopeZero($query)
    {
        return $query->where('value', 0);
    }
    
    public function scopeValueBetween($query, float $min, float $max)
    {
        return $query->whereBetween('value', [$min, $max]);
    }
}