<?php

namespace App\Models\Traits;

trait WorkerAttributes
{
    public function getFullNameAttribute(): string
    {
        return trim("{$this->last_name} {$this->first_name} {$this->patronymic}");
    }
    
    public function getShortNameAttribute(): string
    {
        $firstLetter = $this->first_name ? mb_substr($this->first_name, 0, 1) . '.' : '';
        return trim("{$this->last_name} {$firstLetter}");
    }
    
    public function getGenderTextAttribute(): string
    {
        return $this->gender == 0 ? 'Мужской' : 'Женский';
    }
    
    public function getMaxExperienceAttribute(): int
    {
        return max(0, $this->age - 18);
    }
    
    public function isValidExperience(): bool
    {
        return $this->experience <= $this->max_experience;
    }
}