<?php

namespace App\Services;

class GaussSolver
{
    public function solve(array $matrix, int $n): array
    {
        for ($k = 0; $k < $n; $k++) {
            // Поиск строки с ненулевым элементом в столбце k
            $maxRow = $k;
            for ($i = $k + 1; $i < $n; $i++) {
                if (abs($matrix[$i][$k]) > abs($matrix[$maxRow][$k])) {
                    $maxRow = $i;
                }
            }
            
            // Если нашли ненулевой элемент, меняем строки местами
            if (abs($matrix[$maxRow][$k]) > 0.0001 && $maxRow != $k) {
                $matrix = $this->swapRows($matrix, $k, $maxRow);
            }
            
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
    
    private function swapRows(array $matrix, int $row1, int $row2): array
    {
        $temp = $matrix[$row1];
        $matrix[$row1] = $matrix[$row2];
        $matrix[$row2] = $temp;
        return $matrix;
    }
}