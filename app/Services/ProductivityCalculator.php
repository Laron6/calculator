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
        if ($n < 2) {
            return array_fill(0, $n, 0);
        }
        
        $hasData = false;
        foreach ($this->bVec as $val) {
            if ($val > 0) {
                $hasData = true;
                break;
            }
        }
        
        if (!$hasData) {
            return array_fill(0, $n, 0);
        }
        
        $matrix = $this->createMatrix($n);
        
        $det = $this->getDeterminant($matrix, $n);
        if (abs($det) < 0.0001) {
            return array_fill(0, $n, 0);
        }
        
        $matrix = $this->gaussianElimination($matrix, $n);
        $this->decisions = $this->backSubstitution($matrix, $n);
        
        foreach ($this->decisions as $i => $val) {
            if ($val < 0) {
                $this->decisions[$i] = 0;
            }
        }
        
        return $this->decisions;
    }
    
    private function getDeterminant($matrix, $n)
    {
        $temp = [];
        for ($i = 0; $i < $n; $i++) {
            $temp[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $temp[$i][$j] = $matrix[$i][$j];
            }
        }
        
        $det = 1;
        
        for ($i = 0; $i < $n; $i++) {
            $pivot = $temp[$i][$i];
            if (abs($pivot) < 0.0001) {
                $swap = -1;
                for ($j = $i + 1; $j < $n; $j++) {
                    if (abs($temp[$j][$i]) > 0.0001) {
                        $swap = $j;
                        break;
                    }
                }
                if ($swap == -1) return 0;
                $temp = $this->swapRows($temp, $i, $swap);
                $det = -$det;
                $pivot = $temp[$i][$i];
            }
            
            $det *= $pivot;
            
            for ($j = $i + 1; $j < $n; $j++) {
                $factor = $temp[$j][$i] / $pivot;
                for ($k = $i; $k < $n; $k++) {
                    $temp[$j][$k] -= $factor * $temp[$i][$k];
                }
            }
        }
        
        return $det;
    }
    
    private function swapRows($matrix, $row1, $row2)
    {
        $temp = $matrix[$row1];
        $matrix[$row1] = $matrix[$row2];
        $matrix[$row2] = $temp;
        return $matrix;
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
    
    private function gaussianElimination($matrix, $n)
    {
        for ($k = 0; $k < $n; $k++) {
            $pivot = $matrix[$k][$k];
            if (abs($pivot) < 0.0001) {
                continue;
            }
            
            for ($i = $n - 1; $i >= $k; $i--) {
                if ($i == $k) {
                    for ($j = $n; $j >= $k; $j--) {
                        $matrix[$i][$j] /= $pivot;
                    }
                } else {
                    $factor = $matrix[$i][$k] / $pivot;
                    for ($j = $n; $j >= $k; $j--) {
                        $matrix[$i][$j] -= $factor * $matrix[$k][$j];
                    }
                }
            }
        }
        return $matrix;
    }
    
    private function backSubstitution($matrix, $n)
    {
        $decisions = array_fill(0, $n, 0);
        
        for ($i = $n - 1; $i >= 0; $i--) {
            $decisions[$i] = $matrix[$i][$n];
            for ($j = $i + 1; $j < $n; $j++) {
                $decisions[$i] -= $matrix[$i][$j] * $decisions[$j];
            }
            if (is_nan($decisions[$i]) || is_infinite($decisions[$i]) || $decisions[$i] < 0) {
                $decisions[$i] = 0;
            } else {
                $decisions[$i] = round($decisions[$i], 2);
            }
        }
        
        return $decisions;
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
        
        $decisions = array_fill(0, $n, 0);
        $thirdIdx = min(2, $n - 1);
        
        $del = $n;
        
        if ($del != 0 && isset($this->bVec[$thirdIdx]) && $this->bVec[$thirdIdx] > 0) {
            for ($i = 0; $i < $n; $i++) {
                $decisions[$i] = $this->bVec[$thirdIdx] / $del;
            }
        }
        
        if ($n > 4 && isset($this->bVec[0]) && $this->bVec[0] > 0) {
            $temp = $this->bVec[0];
            for ($i = 0; $i < $n; $i++) {
                if ($i != 4 && isset($decisions[$i])) {
                    $temp -= $decisions[$i];
                }
            }
            $decisions[4] = $temp;
        }
        
        if ($n > 5 && isset($this->bVec[1]) && $this->bVec[1] > 0) {
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