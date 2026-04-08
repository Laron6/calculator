<?php

use App\Http\Controllers\Api\CalculatorController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/calculate/{groupId}', [CalculatorController::class, 'calculate']);
    Route::get('/calculate-alternative/{groupId}', [CalculatorController::class, 'calculateAlternative']);
});