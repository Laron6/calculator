<?php

namespace App\Services;

use App\Models\WorkGroup;
use App\Models\GroupProductivity;

class ProductivityCalculator
{
    private $group;
    private $bVec = [];
    private $decisions = [];
    
    public function __construct(WorkGroup $group)
    {
        $this->group = $group->load('workers');
        $this->loadProductivityData();
    }
    
    private function loadProductivityData()
    {
        $workers = $this->group->workers;
        foreach ($workers as $index => $worker) {
            $prod = GroupProductivity::where('work_group_id', $this->group->id)
                ->where('worker_id', $worker->id)
                ->first();
            $this->bVec[$index] = $prod ? (float)$prod->value : 0;
        }
    }
    
    public function calculate()
    {
        $n = count($this->group->workers);
        if ($n === 0) return [];
        
        $matrix = $this->createMatrix($n);
        
        for ($k = 0; $k < $n; $k++) {
            for ($i = $n - 1; $i >= $k; $i--) {
                if ($i == $k) {
                    $pivot = $matrix[$k][$k];
                    if ($pivot != 0) {
                        for ($j = $n; $j >= $k; $j--) {
                            $matrix[$i][$j] /= $pivot;
                        }
                    }
                } else {
                    $factor = $matrix[$i][$k] / $matrix[$k][$k];
                    for ($j = $n; $j >= $k; $j--) {
                        $matrix[$i][$j] -= $factor * $matrix[$k][$j];
                    }
                }
            }
        }
        
        $this->decisions = array_fill(0, $n, 0);
        for ($i = $n - 1; $i >= 0; $i--) {
            $this->decisions[$i] = $matrix[$i][$n];
            for ($j = $i + 1; $j < $n; $j++) {
                $this->decisions[$i] -= $matrix[$i][$j] * $this->decisions[$j];
            }
            $this->decisions[$i] = round($this->decisions[$i], 2);
        }
        
        return $this->decisions;
    }
    
    private function createMatrix($n)
    {
        $matrix = [];
        
        for ($i = 0; $i < $n; $i++) {
            $matrix[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $matrix[$i][$j] = ($i == $j) ? 0 : 1;
            }
            $matrix[$i][$n] = $this->bVec[$i] ?? 0;
        }
        
        for ($i = 0; $i < $n - 1; $i++) {
            for ($j = 0; $j <= $n; $j++) {
                $matrix[$i][$j] += $matrix[$i + 1][$j];
            }
        }
        
        for ($j = 0; $j <= $n; $j++) {
            $matrix[$n - 1][$j] += $matrix[0][$j];
        }
        
        return $matrix;
    }
    
    public function calculateMetrics()
    {
        if (empty($this->decisions)) {
            $this->calculate();
        }
        
        $n = count($this->decisions);
        if ($n < 4) return ['L' => 0, 'R' => 0];
        
        $last4 = array_slice($this->decisions, -4);
        
        $L = 0.3 * ($last4[0] ?? 0) + 
             0.3 * ($last4[1] ?? 0) + 
             0.5 * ($last4[2] ?? 0) + 
             0.5 * ($last4[3] ?? 0);
        
        $R = (($last4[0] ?? 0) + 
              ($last4[1] ?? 0) + 
              ($last4[2] ?? 0) + 
              0.5 * ($last4[3] ?? 0)) / 1860;
        
        return [
            'L' => round($L, 2),
            'R' => (int)$R
        ];
    }
    
    public function alternativeCalculate()
    {
        $n = count($this->group->workers);
        if ($n < 3) return ['decisions' => [], 'L' => 0, 'R' => 0];
        
        $workers = $this->group->workers->values();
        
        $del = 0;
        for ($j = 0; $j < $n; $j++) {
            $del += 1;
        }
        
        $decisions = array_fill(0, $n, 0);
        $thirdIdx = min(2, $n - 1);
        
        if ($del != 0 && isset($this->bVec[$thirdIdx])) {
            for ($i = 0; $i < $n; $i++) {
                $decisions[$i] = $this->bVec[$thirdIdx] / $del;
            }
        }
        
        if ($n > 4 && isset($this->bVec[0])) {
            $temp = $this->bVec[0];
            for ($i = 0; $i < $n; $i++) {
                if ($i != 4 && isset($decisions[$i])) {
                    $temp -= $decisions[$i];
                }
            }
            $decisions[4] = $temp;
        }
        
        if ($n > 5 && isset($this->bVec[1])) {
            $temp = $this->bVec[1];
            for ($i = 0; $i < $n; $i++) {
                if ($i != 5 && isset($decisions[$i])) {
                    $temp -= $decisions[$i];
                }
            }
            $decisions[5] = $temp;
        }
        
        $last4 = array_slice($decisions, -4);
        $L = 0.3 * ($last4[0] ?? 0) + 
             0.3 * ($last4[1] ?? 0) + 
             0.5 * ($last4[2] ?? 0) + 
             0.5 * ($last4[3] ?? 0);
        
        $R = (($last4[0] ?? 0) + 
              ($last4[1] ?? 0) + 
              ($last4[2] ?? 0) + 
              0.5 * ($last4[3] ?? 0)) / 1860;
        
        return [
            'decisions' => $decisions,
            'L' => round($L, 2),
            'R' => (int)$R
        ];
    }
    
    public function getBVec()
    {
        return $this->bVec;
    }
    
    public function getDecisions()
    {
        return $this->decisions;
    }
}