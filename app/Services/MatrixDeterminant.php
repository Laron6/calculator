<?php

namespace App\Services;

class MatrixDeterminant
{
    public function calculate(array $matrix, int $n): float
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
    
    private function swapRows(array $matrix, int $row1, int $row2): array
    {
        $temp = $matrix[$row1];
        $matrix[$row1] = $matrix[$row2];
        $matrix[$row2] = $temp;
        return $matrix;
    }
}