<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Services\GroupService;
use App\Http\Requests\GroupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GroupController extends Controller
{
    protected $groupService;
    
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }
    
    public function createGroup(GroupRequest $request)
    {
        try {
            $this->groupService->create($request->validated());
            return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Группа создана');
        } catch (\Exception $e) {
            Log::error('Ошибка создания группы: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при создании группы')->withInput();
        }
    }
    
    public function editGroup($id)
    {
        try {
            $group = WorkGroup::findOrFail($id);
            return view('pages.edit-group', compact('group'));
        } catch (\Exception $e) {
            Log::error('Ошибка редактирования группы: ' . $e->getMessage());
            return redirect()->route('home')->with('error', 'Группа не найдена');
        }
    }
    
    public function updateGroup(GroupRequest $request, $id)
    {
        try {
            $group = WorkGroup::findOrFail($id);
            $this->groupService->update($group, $request->validated());
            return redirect()->route('home', ['tab' => 'workers', 'group_id' => $id])->with('success', 'Группа переименована');
        } catch (\Exception $e) {
            Log::error('Ошибка обновления группы: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при обновлении группы')->withInput();
        }
    }
    
    public function deleteGroup($id)
    {
        try {
            $group = WorkGroup::findOrFail($id);
            $this->groupService->delete($group);
            return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Группа удалена');
        } catch (\Exception $e) {
            Log::error('Ошибка удаления группы: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при удалении группы');
        }
    }
    
    public function addToGroup(Request $request, $groupId)
    {
        try {
            $request->validate(['worker_id' => 'required|exists:workers,id']);
            $group = WorkGroup::findOrFail($groupId);
            $this->groupService->addWorker($group, $request->worker_id);
            return redirect()->route('home', ['tab' => 'workers', 'group_id' => $groupId])->with('success', 'Рабочий добавлен в группу');
        } catch (\Exception $e) {
            Log::error('Ошибка добавления рабочего в группу: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при добавлении рабочего в группу');
        }
    }
    
    public function removeFromGroup(Request $request, $groupId)
    {
        try {
            $request->validate(['worker_id' => 'required|exists:workers,id']);
            $group = WorkGroup::findOrFail($groupId);
            $this->groupService->removeWorker($group, $request->worker_id);
            return redirect()->route('home', ['tab' => 'workers', 'group_id' => $groupId])->with('success', 'Рабочий удален из группы');
        } catch (\Exception $e) {
            Log::error('Ошибка удаления рабочего из группы: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при удалении рабочего из группы');
        }
    }
}