<?php

namespace App\Services;

use App\Models\WorkGroup;
use App\Models\GroupProductivity;

class ProductivitySolver
{
    private WorkGroup $group;
    private array $bVec = [];
    private array $decisions = [];
    
    public function __construct(WorkGroup $group)
    {
        $this->group = $group->load('workers');
        $this->loadProductivityData();
    }
    
    /**
     * Загрузка данных производительности (объём и время)
     */
    private function loadProductivityData(): void
    {
        $workers = $this->group->workers;
        
        foreach ($workers as $index => $worker) {
            $prod = GroupProductivity::where('work_group_id', $this->group->id)
                ->where('worker_id', $worker->id)
                ->first();
            
            // Сохраняем объём и время для каждого рабочего
            if ($prod) {
                $this->bVec[$index] = [
                    'volume' => $prod->volume ?? 0,
                    'time' => $prod->time ?? 0,
                    'productivity' => $prod->value ?? 0
                ];
            } else {
                $this->bVec[$index] = [
                    'volume' => 0,
                    'time' => 0,
                    'productivity' => 0
                ];
            }
        }
    }
    
    /**
     * Расчёт производительности по формуле ПТ = V / T
     * 
     * @return array Массив производительности для каждого рабочего
     */
    public function calculateProductivity(): array
    {
        $n = count($this->group->workers);
        $this->decisions = [];
        
        foreach ($this->group->workers as $index => $worker) {
            $volume = $this->bVec[$index]['volume'] ?? 0;
            $time = $this->bVec[$index]['time'] ?? 0;
            
            // Формула: производительность = объём / время
            if ($time > 0 && $volume > 0) {
                $productivity = $volume / $time;
            } else {
                $productivity = 0;
            }
            
            $this->decisions[$index] = round($productivity, 2);
        }
        
        return $this->decisions;
    }
    
    /**
     * Получить производительность всей группы (суммарная)
     */
    public function getTotalProductivity(): float
    {
        $total = 0;
        foreach ($this->decisions as $val) {
            $total += $val;
        }
        return round($total, 2);
    }
    
    /**
     * Получить среднюю производительность по группе
     */
    public function getAverageProductivity(): float
    {
        if (count($this->decisions) === 0) return 0;
        $total = $this->getTotalProductivity();
        return round($total / count($this->decisions), 2);
    }
    
    /**
     * Получить данные для графиков
     */
    public function getChartData(): array
    {
        $labels = [];
        $productivities = [];
        
        foreach ($this->group->workers as $worker) {
            $labels[] = $worker->short_name;
        }
        
        foreach ($this->decisions as $prod) {
            $productivities[] = $prod;
        }
        
        return [
            'labels' => $labels,
            'productivities' => $productivities
        ];
    }
    
    public function getBVec(): array
    {
        return $this->bVec;
    }
    
    public function getDecisions(): array
    {
        if (empty($this->decisions)) {
            $this->calculateProductivity();
        }
        return $this->decisions;
    }
}