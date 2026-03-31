<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::post('/worker/add', [HomeController::class, 'addWorker']);
Route::get('/worker/edit/{id}', [HomeController::class, 'editWorker']);
Route::post('/worker/update/{id}', [HomeController::class, 'updateWorker']);
Route::get('/worker/delete/{id}', [HomeController::class, 'deleteWorker']);

Route::post('/group/create', [HomeController::class, 'createGroup']);
Route::get('/group/edit/{id}', [HomeController::class, 'editGroup']);
Route::post('/group/update/{id}', [HomeController::class, 'updateGroup']);
Route::get('/group/delete/{id}', [HomeController::class, 'deleteGroup']);

Route::post('/group/{groupId}/add-worker', [HomeController::class, 'addToGroup']);
Route::post('/group/{groupId}/remove-worker', [HomeController::class, 'removeFromGroup']);

Route::post('/group/{groupId}/productivity', [HomeController::class, 'saveProductivity']);
Route::get('/group/{groupId}/calculate', [HomeController::class, 'calculate']);
Route::get('/group/{groupId}/calculate-alternative', [HomeController::class, 'calculateAlternative']);

Route::get('/workers/export', [HomeController::class, 'exportWorkers']);
Route::post('/workers/import', [HomeController::class, 'importWorkers']);

Route::get('/charts', [HomeController::class, 'charts']);