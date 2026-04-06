<?php

namespace App\Services;

class GaussSolver
{
    public function solve(array $matrix, int $n): array
    {
        for ($k = 0; $k < $n; $k++) {
            $pivot = $matrix[$k][$k];
            if (abs($pivot) < 0.0001) continue;
            
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
    
    public function backSubstitution(array $matrix, int $n): array
    {
        $decisions = array_fill(0, $n, 0);
        
        for ($i = $n - 1; $i >= 0; $i--) {
            $decisions[$i] = $matrix[$i][$n];
            for ($j = $i + 1; $j < $n; $j++) {
                $decisions[$i] -= $matrix[$i][$j] * $decisions[$j];
            }
        }
        return $decisions;
    }
}