<?php

namespace App\Models\Traits;

trait WorkerScopes
{
    public function scopeMale($query)
    {
        return $query->where('gender', 0);
    }
    
    public function scopeFemale($query)
    {
        return $query->where('gender', 1);
    }
    
    public function scopeAgeBetween($query, int $min, int $max)
    {
        return $query->whereBetween('age', [$min, $max]);
    }
    
    public function scopeMinAge($query, int $age)
    {
        return $query->where('age', '>=', $age);
    }
    
    public function scopeMaxAge($query, int $age)
    {
        return $query->where('age', '<=', $age);
    }
    
    public function scopeExperienceBetween($query, int $min, int $max)
    {
        return $query->whereBetween('experience', [$min, $max]);
    }
    
    public function scopeSearch($query, string $search)
    {
        return $query->where('last_name', 'like', "%{$search}%")
            ->orWhere('first_name', 'like', "%{$search}%")
            ->orWhere('patronymic', 'like', "%{$search}%");
    }
    
    public function scopeOrderByLastName($query, string $direction = 'asc')
    {
        return $query->orderBy('last_name', $direction);
    }
    
    public function scopeAdult($query)
    {
        return $query->where('age', '>=', 18);
    }
    
    public function scopeRetirementAge($query)
    {
        return $query->where(function($q) {
            $q->where('gender', 0)->where('age', '>=', 60)
              ->orWhere('gender', 1)->where('age', '>=', 55);
        });
    }
}