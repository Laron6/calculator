<?php

namespace App\Models\Traits;

trait WorkGroupAttributes
{
    public function getWorkersCountAttribute(): int
    {
        return $this->workers->count();
    }
    
    public function getAverageAgeAttribute(): float
    {
        return round($this->workers->avg('age') ?? 0, 1);
    }
    
    public function getAverageExperienceAttribute(): float
    {
        return round($this->workers->avg('experience') ?? 0, 1);
    }
    
    public function getMenCountAttribute(): int
    {
        return $this->workers->where('gender', 0)->count();
    }
    
    public function getWomenCountAttribute(): int
    {
        return $this->workers->where('gender', 1)->count();
    }
    
    public function getHasProductivityDataAttribute(): bool
    {
        return $this->productivities()->exists();
    }
}