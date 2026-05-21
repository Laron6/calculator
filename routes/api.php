<?php

use App\Http\Controllers\Api\CalculatorController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::get('/calculate/{groupId}', [CalculatorController::class, 'calculate']);
});