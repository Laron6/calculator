<?php

namespace App\Services;

use Illuminate\Support\Facades\Config;

class MetricsCalculator
{
    public function calculate(array $decisions): array
    {
        $n = count($decisions);
        
        if ($n < 4) {
            return ['L' => 0, 'R' => 0];
        }
        
        // Получаем коэффициенты из конфига
        $lCoeff = Config::get('productivity.l_coefficients', [0.3, 0.3, 0.5, 0.5]);
        $rDivisor = Config::get('productivity.r_divisor', 1860);
        $rX9Coeff = Config::get('productivity.r_x9_coefficient', 0.5);
        
        if ($n >= 9) {
            $x6 = $decisions[5] ?? 0;
            $x7 = $decisions[6] ?? 0;
            $x8 = $decisions[7] ?? 0;
            $x9 = $decisions[8] ?? 0;
        } else {
            $lastIdx = $n - 1;
            $x9 = $decisions[$lastIdx] ?? 0;
            $x8 = ($n >= 2) ? ($decisions[$lastIdx - 1] ?? 0) : 0;
            $x7 = ($n >= 3) ? ($decisions[$lastIdx - 2] ?? 0) : 0;
            $x6 = ($n >= 4) ? ($decisions[$lastIdx - 3] ?? 0) : 0;
        }
        
        $L = $lCoeff[0] * $x6 + $lCoeff[1] * $x7 + $lCoeff[2] * $x8 + $lCoeff[3] * $x9;
        $R = ($x6 + $x7 + $x8 + $rX9Coeff * $x9) / $rDivisor;
        
        return [
            'L' => round($L, 2),
            'R' => (int)$R
        ];
    }
}