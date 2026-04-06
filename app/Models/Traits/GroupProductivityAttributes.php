<?php

namespace App\Models\Traits;

trait GroupProductivityAttributes
{
    public function getFormattedValueAttribute(): string
    {
        return number_format($this->value, 2, '.', '');
    }
    
    public function getHasValueAttribute(): bool
    {
        return $this->value !== null && $this->value > 0;
    }
}