<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\ProductivityController;
use App\Http\Controllers\ImportExportController;
use App\Http\Controllers\ChartController;
use Illuminate\Support\Facades\Route;

// Главная страница
Route::get('/', [HomeController::class, 'index'])->name('home');

// Группа маршрутов для рабочих
Route::prefix('worker')->group(function () {
    Route::post('/add', [WorkerController::class, 'store'])->name('worker.store');
    Route::get('/edit/{id}', [WorkerController::class, 'edit'])->name('worker.edit');
    Route::post('/update/{id}', [WorkerController::class, 'update'])->name('worker.update');
    Route::delete('/delete/{id}', [WorkerController::class, 'destroy'])->name('worker.destroy');
});

// Группа маршрутов для групп
Route::prefix('group')->group(function () {
    Route::post('/create', [GroupController::class, 'store'])->name('group.store');
    Route::get('/edit/{id}', [GroupController::class, 'edit'])->name('group.edit');
    Route::post('/update/{id}', [GroupController::class, 'update'])->name('group.update');
    Route::delete('/delete/{id}', [GroupController::class, 'destroy'])->name('group.destroy');
    
    // Состав группы
    Route::post('/{groupId}/add-worker', [GroupController::class, 'addWorker'])->name('group.addWorker');
    Route::post('/{groupId}/remove-worker', [GroupController::class, 'removeWorker'])->name('group.removeWorker');
    
    // Производительность
    Route::post('/{groupId}/productivity', [ProductivityController::class, 'saveProductivity'])->name('productivity.save');
    Route::get('/{groupId}/calculate', [ProductivityController::class, 'calculate'])->name('productivity.calculate');
    Route::get('/{groupId}/calculate-alternative', [ProductivityController::class, 'calculateAlternative'])->name('productivity.calculateAlternative');
});

// Импорт/экспорт
Route::get('/workers/export', [ImportExportController::class, 'exportWorkers'])->name('workers.export');
Route::post('/workers/import', [ImportExportController::class, 'importWorkers'])->name('workers.import');

// Графики
Route::get('/charts', [ChartController::class, 'charts'])->name('charts');