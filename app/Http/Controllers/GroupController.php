<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Models\Worker;
use App\Models\GroupProductivity;
use App\Services\GroupService;
use App\Http\Requests\GroupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupController extends Controller
{
    protected $groupService;
    
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }
    
    public function store(GroupRequest $request)
    {
        $this->groupService->create($request->validated());
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Группа создана');
    }
    
    public function edit($id)
    {
        $group = WorkGroup::findOrFail($id);
        return view('pages.edit-group', compact('group'));
    }
    
    public function update(GroupRequest $request, $id)
    {
        $group = WorkGroup::findOrFail($id);
        $this->groupService->update($group, $request->validated());
        return redirect()->route('home', ['tab' => 'workers', 'group_id' => $id])->with('success', 'Группа переименована');
    }
    
    public function destroy($id)
    {
        $group = WorkGroup::findOrFail($id);
        $this->groupService->delete($group);
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Группа удалена');
    }
    
    public function addWorker(Request $request, $groupId)
    {
        $request->validate(['worker_id' => 'required|exists:workers,id']);
        
        $group = WorkGroup::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->first();
        
        if (!$group) {
            abort(403, 'У вас нет прав на добавление рабочих в эту группу');
        }
        
        $worker = Worker::where('id', $request->worker_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();
        
        // Добавляем рабочего в группу
        $group->workers()->attach($worker->id);
        
        // Создаём запись производительности за сегодня
        GroupProductivity::updateOrCreate(
            [
                'work_group_id' => $groupId,
                'worker_id' => $worker->id,
                'record_date' => now()->format('Y-m-d'),
            ],
            [
                'volume' => 0,
                'time' => 0,
                'value' => 0,
                'user_id' => Auth::id(),
            ]
        );
        
        return redirect()->route('home', ['tab' => 'workers', 'group_id' => $groupId])->with('success', 'Рабочий добавлен в группу');
    }
    
    public function removeWorker(Request $request, $groupId)
    {
        $request->validate(['worker_id' => 'required|exists:workers,id']);
        
        $group = WorkGroup::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->first();
        
        if (!$group) {
            abort(403, 'У вас нет прав на удаление рабочих из этой группы');
        }
        
        $this->groupService->removeWorker($group, $request->worker_id);
        return redirect()->route('home', ['tab' => 'workers', 'group_id' => $groupId])->with('success', 'Рабочий удален из группы');
    }
}