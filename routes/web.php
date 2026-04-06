<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProductivityController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\ChartController;
use App\Http\Controllers\Api\CalculatorController;
use Illuminate\Support\Facades\Route;

// Главная страница
Route::get('/', [HomeController::class, 'index'])->name('home');

// Web

// Рабочие
Route::post('/worker/add', [WorkerController::class, 'addWorker']);
Route::get('/worker/edit/{id}', [WorkerController::class, 'editWorker']);
Route::post('/worker/update/{id}', [WorkerController::class, 'updateWorker']);
Route::get('/worker/delete/{id}', [WorkerController::class, 'deleteWorker']);

// Группы
Route::post('/group/create', [GroupController::class, 'createGroup']);
Route::get('/group/edit/{id}', [GroupController::class, 'editGroup']);
Route::post('/group/update/{id}', [GroupController::class, 'updateGroup']);
Route::get('/group/delete/{id}', [GroupController::class, 'deleteGroup']);

// Состав группы
Route::post('/group/{groupId}/add-worker', [GroupController::class, 'addToGroup']);
Route::post('/group/{groupId}/remove-worker', [GroupController::class, 'removeFromGroup']);

// Производительность
Route::post('/group/{groupId}/productivity', [ProductivityController::class, 'saveProductivity']);
Route::get('/group/{groupId}/calculate', [ProductivityController::class, 'calculate']);
Route::get('/group/{groupId}/calculate-alternative', [ProductivityController::class, 'calculateAlternative']);

// Импорт/экспорт
Route::get('/workers/export', [ImportExportController::class, 'exportWorkers']);
Route::post('/workers/import', [ImportExportController::class, 'importWorkers']);

// Графики
Route::get('/charts', [ChartController::class, 'charts']);

// Api-маршруты
Route::prefix('api')->group(function () {
    Route::get('/calculate/{groupId}', [CalculatorController::class, 'calculate']);
    Route::get('/calculate-alternative/{groupId}', [CalculatorController::class, 'calculateAlternative']);
});