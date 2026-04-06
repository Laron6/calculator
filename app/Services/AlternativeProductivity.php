<?php

namespace App\Services;

class AlternativeProductivity
{
    private array $koeff;
    private array $D;
    private array $X = [];
    private array $Y = [];
    
    public function __construct()
    {
        $this->koeff = config('productivity.koeff');
        $this->D = config('productivity.D');
        
        for ($i = 0; $i < 9; $i++) {
            $this->X[$i] = 0;
            $this->Y[$i] = false;
        }
    }
    
    public function calculate(): array
    {
        $opt = 2;
        $del = $this->sum($opt);
        
        if (abs($del) > 0.0001) {
            for ($i = 0; $i < 9; $i++) {
                $this->X[$i] = $this->koeff[$opt][$i] * $this->D[$opt] / $del;
                $this->Y[$i] = ($this->koeff[$opt][$i] != 0);
            }
        }
        
        $idx5 = 4;
        if (!$this->Y[$idx5]) {
            $mnozh = $this->D[0];
            for ($j = 0; $j < 5; $j++) {
                $mnozh -= $this->koeff[0][$j] * $this->X[$j];
            }
            $this->X[$idx5] = $mnozh;
            $this->Y[$idx5] = true;
        }
        
        $idx6 = 5;
        if (!$this->Y[$idx6]) {
            $mnozh = $this->D[1];
            for ($j = 0; $j < 5; $j++) {
                $mnozh -= $this->koeff[1][$j] * $this->X[$j];
            }
            $this->X[$idx6] = $mnozh;
            $this->Y[$idx6] = true;
        }
        
        $x5 = $this->X[4] ?? 0;
        $x6 = $this->X[5] ?? 0;
        $x7 = $this->X[6] ?? 0;
        $x8 = $this->X[7] ?? 0;
        
        $L = 0.3 * $x5 + 0.3 * $x6 + 0.5 * $x7 + 0.5 * $x8;
        $R = ($x5 + $x6 + $x7 + 0.5 * $x8) / 1860;
        
        return [
            'X' => $this->X,
            'L' => round($L, 2),
            'R' => (int)$R
        ];
    }
    
    private function sum(int $nRow): float
    {
        $result = 0;
        for ($j = 0; $j < 9; $j++) {
            $result += pow($this->koeff[$nRow][$j], 2);
        }
        return $result;
    }
}