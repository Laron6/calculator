<?php

namespace App\Models\Traits;

trait WorkGroupScopes
{
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
    
    public function scopeOrderByName($query, string $direction = 'asc')
    {
        return $query->orderBy('name', $direction);
    }
    
    public function scopeWithMinWorkers($query, int $count)
    {
        return $query->has('workers', '>=', $count);
    }
    
    public function scopeWithMaxWorkers($query, int $count)
    {
        return $query->has('workers', '<=', $count);
    }
    
    public function scopeWithProductivity($query)
    {
        return $query->has('productivities');
    }
    
    public function scopeWithoutProductivity($query)
    {
        return $query->doesntHave('productivities');
    }
}