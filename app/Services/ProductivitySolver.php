<?php

namespace App\Services;

use App\Models\WorkGroup;
use App\Models\GroupProductivity;

class ProductivitySolver
{
    private WorkGroup $group;
    private array $bVec = [];
    private array $decisions = [];
    private array $koeff;
    private array $D;
    
    private GaussSolver $gauss;
    private MatrixDeterminant $determinant;
    private MetricsCalculator $metrics;
    private AlternativeProductivity $alternative;
    
    public function __construct(WorkGroup $group)
    {
        $this->group = $group->load('workers');
        $this->loadProductivityData();
        
        $this->koeff = config('productivity.koeff');
        $this->D = config('productivity.D');
        
        $this->gauss = new GaussSolver();
        $this->determinant = new MatrixDeterminant();
        $this->metrics = new MetricsCalculator();
        $this->alternative = new AlternativeProductivity();
    }
    
    private function loadProductivityData(): void
    {
        $workers = $this->group->workers;
        
        foreach ($workers as $index => $worker) {
            $prod = GroupProductivity::where('work_group_id', $this->group->id)
                ->where('worker_id', $worker->id)
                ->first();
            $this->bVec[$index] = $prod ? (float)$prod->value : 0;
        }
        
        for ($i = count($this->bVec); $i < 9; $i++) {
            $this->bVec[$i] = 0;
        }
    }
    
    public function solveByGauss(): array
    {
        $n = count($this->group->workers);
        
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
        $matrix = $this->gauss->solve($matrix, $n);
        $this->decisions = $this->gauss->backSubstitution($matrix, $n);
        
        foreach ($this->decisions as $i => $val) {
            if ($val < 0) $this->decisions[$i] = 0;
            $this->decisions[$i] = round($this->decisions[$i], 2);
        }
        
        return array_slice($this->decisions, 0, $n);
    }
    
    private function createMatrix(int $n): array
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
    
    public function solveByCramer(): array
    {
        $n = count($this->group->workers);
        if ($n === 0) return [];
        
        $matrix = [];
        for ($i = 0; $i < $n; $i++) {
            $matrix[$i] = [];
            for ($j = 0; $j < $n; $j++) {
                $matrix[$i][$j] = ($i == $j) ? 0 : 1;
            }
        }
        
        $bVecSlice = array_slice($this->bVec, 0, $n);
        $cramer = new CramerSolver();
        $decisions = $cramer->solve($matrix, $bVecSlice);
        
        foreach ($decisions as $i => $val) {
            if ($val < 0) $decisions[$i] = 0;
            $decisions[$i] = round($decisions[$i], 2);
        }
        
        $this->decisions = $decisions;
        return $this->decisions;
    }
    
    public function getMetrics(): array
    {
        if (empty($this->decisions)) {
            $this->solveByGauss();
        }
        return $this->metrics->calculate($this->decisions);
    }
    
    public function solveAlternative(): array
    {
        $n = count($this->group->workers);
        if ($n < 3) return ['decisions' => [], 'L' => 0, 'R' => 0];
        
        $result = $this->alternative->calculate();
        $decisions = [];
        
        for ($i = 0; $i < $n; $i++) {
            $val = ($i < 9) ? ($result['X'][$i] ?? 0) : 0;
            if ($val < 0) $val = 0;
            $decisions[] = round($val, 2);
        }
        
        return [
            'decisions' => $decisions,
            'L' => $result['L'],
            'R' => $result['R']
        ];
    }
    
    public function getBVec(): array
    {
        return $this->bVec;
    }
    
    public function getDecisions(): array
    {
        return $this->decisions;
    }
}