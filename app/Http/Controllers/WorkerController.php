<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Services\WorkerService;
use App\Http\Requests\WorkerRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class WorkerController extends Controller
{
    protected $workerService;
    
    public function __construct(WorkerService $workerService)
    {
        $this->workerService = $workerService;
    }
    
    public function addWorker(WorkerRequest $request)
    {
        try {
            $this->workerService->create($request->validated());
            return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий добавлен');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Ошибка добавления рабочего: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при добавлении рабочего')->withInput();
        }
    }
    
    public function editWorker($id)
    {
        try {
            $worker = Worker::findOrFail($id);
            return view('pages.edit-worker', compact('worker'));
        } catch (\Exception $e) {
            Log::error('Ошибка редактирования рабочего: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Рабочий не найден');
        }
    }
    
    public function updateWorker(WorkerRequest $request, $id)
    {
        try {
            $worker = Worker::findOrFail($id);
            $this->workerService->update($worker, $request->validated());
            return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий обновлен');
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Ошибка обновления рабочего: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при обновлении рабочего')->withInput();
        }
    }
    
    public function deleteWorker($id)
    {
        try {
            $worker = Worker::findOrFail($id);
            $this->workerService->delete($worker);
            return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий удален');
        } catch (\Exception $e) {
            Log::error('Ошибка удаления рабочего: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при удалении рабочего');
        }
    }
}