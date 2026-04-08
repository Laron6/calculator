<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Services\WorkerService;
use App\Http\Requests\WorkerRequest;

class WorkerController extends Controller
{
    protected $workerService;
    
    public function __construct(WorkerService $workerService)
    {
        $this->workerService = $workerService;
    }
    
    public function store(WorkerRequest $request)
    {
        $this->workerService->create($request->validated());
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий добавлен');
    }
    
    public function edit($id)
    {
        $worker = Worker::findOrFail($id);
        return view('pages.edit-worker', compact('worker'));
    }
    
    public function update(WorkerRequest $request, $id)
    {
        $worker = Worker::findOrFail($id);
        $this->workerService->update($worker, $request->validated());
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий обновлен');
    }
    
    public function destroy($id)
    {
        $worker = Worker::findOrFail($id);
        $this->workerService->delete($worker);
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий удален');
    }
}