<?php

namespace App\Services;

class CramerSolver
{
    public function solve(array $matrix, array $bVec): array
    {
        $n = count($matrix);
        $detA = $this->getDeterminant($matrix);
        
        if (abs($detA) < 0.0001) {
            return array_fill(0, $n, 0);
        }
        
        $decisions = array_fill(0, $n, 0);
        
        for ($k = 0; $k < $n; $k++) {
            $tempMatrix = $matrix;
            for ($j = 0; $j < $n; $j++) {
                $tempMatrix[$j][$k] = $bVec[$j];
            }
            $det = $this->getDeterminant($tempMatrix);
            $decisions[$k] = $det / $detA;
        }
        
        return $decisions;
    }
    
    private function getDeterminant(array $matrix): float
    {
        $n = count($matrix);
        $skipCols = array_fill(0, $n, false);
        $skipRows = array_fill(0, $n, false);
        $result = 0;
        
        for ($j = 0; $j < $n; $j++) {
            $sign = ($j % 2 == 0) ? 1 : -1;
            $result += $sign * $matrix[0][$j] * $this->getMinor($matrix, 0, $j, $skipCols, $skipRows);
        }
        
        return $result;
    }
    
    private function getMinor(array $matrix, int $row, int $col, array &$skipCols, array &$skipRows): float
    {
        $n = count($matrix);
        $skipCols[$col] = true;
        $skipRows[$row] = true;
        
        $j = 0;
        for ($i = 0; $i < $n; $i++) {
            if (!$skipCols[$i]) $j++;
        }
        
        if ($j == 2) {
            $lowestMinor = [[0, 0], [0, 0]];
            $r = 0;
            
            for ($i = 0; $i < $n; $i++) {
                if ($skipRows[$i]) continue;
                $c = 0;
                for ($j = 0; $j < $n; $j++) {
                    if ($skipCols[$j]) continue;
                    $lowestMinor[$r][$c] = $matrix[$i][$j];
                    $c++;
                }
                $r++;
            }
            
            return $lowestMinor[0][0] * $lowestMinor[1][1] - $lowestMinor[1][0] * $lowestMinor[0][1];
        }
        
        $result = 0;
        $c = 0;
        $r = 0;
        
        for ($i = 0; $i < $n; $i++) {
            if ($skipRows[$i]) {
                $r++;
                continue;
            }
            
            for ($j = 0; $j < $n; $j++) {
                if ($skipCols[$j]) {
                    $c++;
                    continue;
                }
                
                $sign = (($i + $j - ($c + $r)) % 2 == 0) ? 1 : -1;
                $result += $sign * $matrix[$i][$j] * $this->getMinor($matrix, $i, $j, $skipCols, $skipRows);
            }
            return $result;
        }
        
        return 0;
    }
}