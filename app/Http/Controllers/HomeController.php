<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use App\Services\ProductivityCalculator;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $workers = Worker::orderBy('last_name')->get();
        $groups = WorkGroup::with('workers')->orderBy('name')->get();
        
        $selectedGroupId = $request->get('group_id');
        $selectedGroup = null;
        $productivityValues = [];
        $calculatedResults = [];
        $calculatedMetrics = ['L' => 0, 'R' => 0];
        $alternativeResults = [];
        $showResults = $request->get('calculated') == 1;
        $showAlternative = $request->get('alternative') == 1;
        
        if ($selectedGroupId) {
            $selectedGroup = WorkGroup::with('workers')->find($selectedGroupId);
            if ($selectedGroup) {
                $productivities = GroupProductivity::where('work_group_id', $selectedGroupId)
                    ->get()
                    ->keyBy('worker_id');
                    
                foreach ($selectedGroup->workers as $worker) {
                    $productivityValues[$worker->id] = $productivities[$worker->id]->value ?? '';
                }
                
                if ($showResults) {
                    $calculator = new ProductivityCalculator($selectedGroup);
                    $decisions = $calculator->calculate();
                    $calculatedMetrics = $calculator->calculateMetrics();
                    
                    $workerArray = $selectedGroup->workers->values();
                    foreach ($workerArray as $i => $worker) {
                        $calculatedResults[] = [
                            'worker' => $worker,
                            'productivity' => round($decisions[$i] ?? 0, 2)
                        ];
                    }
                }
                
                if ($showAlternative) {
                    $calculator = new ProductivityCalculator($selectedGroup);
                    $alt = $calculator->alternativeCalculate();
                    $alternativeResults = $alt['decisions'];
                    $calculatedMetrics['L_alt'] = $alt['L'];
                    $calculatedMetrics['R_alt'] = $alt['R'];
                }
            }
        }
        
        $activeTab = $request->get('tab', 'workers');
        
        return view('app', compact(
            'workers', 'groups', 'selectedGroup', 'selectedGroupId',
            'productivityValues', 'calculatedResults', 'calculatedMetrics',
            'showResults', 'showAlternative', 'alternativeResults', 'activeTab'
        ));
    }
    
    public function addWorker(Request $request)
    {
        $request->validate([
            'last_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁa-zA-Z-]+$/u',
            'first_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁa-zA-Z-]+$/u',
            'patronymic' => 'nullable|string|max:50|regex:/^[а-яА-ЯёЁa-zA-Z-]*$/u',
            'age' => 'required|integer|min:18|max:100',
            'experience' => 'required|integer|min:0|max:80',
            'gender' => 'required|in:0,1'
        ]);
        
        Worker::create($request->all());
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий добавлен');
    }
    
    public function editWorker($id)
    {
        $worker = Worker::findOrFail($id);
        return view('edit-worker', compact('worker'));
    }
    
    public function updateWorker(Request $request, $id)
    {
        $request->validate([
            'last_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁa-zA-Z-]+$/u',
            'first_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁa-zA-Z-]+$/u',
            'patronymic' => 'nullable|string|max:50|regex:/^[а-яА-ЯёЁa-zA-Z-]*$/u',
            'age' => 'required|integer|min:18|max:100',
            'experience' => 'required|integer|min:0|max:80',
            'gender' => 'required|in:0,1'
        ]);
        
        $worker = Worker::findOrFail($id);
        $worker->update($request->all());
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий обновлен');
    }
    
    public function deleteWorker($id)
    {
        $worker = Worker::findOrFail($id);
        $worker->groups()->detach();
        GroupProductivity::where('worker_id', $id)->delete();
        $worker->delete();
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий удален');
    }
    
    public function createGroup(Request $request)
    {
        $request->validate(['name' => 'required|string|max:100|regex:/^[а-яА-ЯёЁa-zA-Z0-9\s-]+$/u']);
        WorkGroup::create(['name' => $request->name]);
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Группа создана');
    }
    
    public function editGroup($id)
    {
        $group = WorkGroup::findOrFail($id);
        return view('edit-group', compact('group'));
    }
    
    public function updateGroup(Request $request, $id)
    {
        $request->validate(['name' => 'required|string|max:100|regex:/^[а-яА-ЯёЁa-zA-Z0-9\s-]+$/u']);
        $group = WorkGroup::findOrFail($id);
        $group->update(['name' => $request->name]);
        return redirect()->route('home', ['tab' => 'workers', 'group_id' => $id])->with('success', 'Группа переименована');
    }
    
    public function deleteGroup($id)
    {
        $group = WorkGroup::findOrFail($id);
        $group->workers()->detach();
        GroupProductivity::where('work_group_id', $id)->delete();
        $group->delete();
        return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Группа удалена');
    }
    
    public function addToGroup(Request $request, $groupId)
    {
        $request->validate(['worker_id' => 'required|exists:workers,id']);
        $group = WorkGroup::findOrFail($groupId);
        if (!$group->workers()->where('worker_id', $request->worker_id)->exists()) {
            $group->workers()->attach($request->worker_id);
        }
        return redirect()->route('home', ['tab' => 'workers', 'group_id' => $groupId])->with('success', 'Рабочий добавлен в группу');
    }
    
    public function removeFromGroup(Request $request, $groupId)
    {
        $request->validate(['worker_id' => 'required|exists:workers,id']);
        $group = WorkGroup::findOrFail($groupId);
        $group->workers()->detach($request->worker_id);
        GroupProductivity::where('work_group_id', $groupId)->where('worker_id', $request->worker_id)->delete();
        return redirect()->route('home', ['tab' => 'workers', 'group_id' => $groupId])->with('success', 'Рабочий удален из группы');
    }
    
    public function saveProductivity(Request $request, $groupId)
    {
        $request->validate([
            'productivities' => 'array',
            'productivities.*' => 'nullable|numeric|min:0|max:999999'
        ]);
        
        foreach ($request->productivities as $workerId => $value) {
            if ($value !== null && $value !== '') {
                GroupProductivity::updateOrCreate(
                    ['work_group_id' => $groupId, 'worker_id' => $workerId],
                    ['value' => (float)$value]
                );
            }
        }
        return redirect()->route('home', ['tab' => 'statistics', 'group_id' => $groupId])->with('success', 'Данные сохранены');
    }
    
    public function calculate($groupId)
    {
        return redirect()->route('home', [
            'tab' => 'statistics',
            'group_id' => $groupId,
            'calculated' => 1
        ]);
    }
    
    public function calculateAlternative($groupId)
    {
        return redirect()->route('home', [
            'tab' => 'statistics',
            'group_id' => $groupId,
            'calculated' => 1,
            'alternative' => 1
        ]);
    }
    
    public function exportWorkers()
    {
        $workers = Worker::all();
        $content = '';
        foreach ($workers as $w) {
            $content .= "{$w->last_name};{$w->first_name};{$w->patronymic};{$w->age};{$w->experience};{$w->gender}\n";
        }
        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', 'attachment; filename="workers.lst"');
    }
    
    public function charts(Request $request)
    {
        $groups = WorkGroup::with('workers')->orderBy('name')->get();
        $selectedGroupId = $request->get('group_id');
        $selectedGroup = null;
        $bVec = [];
        $decisions = [];
        $labels = [];
        
        if ($selectedGroupId) {
            $selectedGroup = WorkGroup::with('workers')->find($selectedGroupId);
            if ($selectedGroup && $selectedGroup->workers->count() > 0) {
                $calculator = new ProductivityCalculator($selectedGroup);
                $bVec = $calculator->getBVec();
                $decisions = $calculator->calculate();
                
                foreach ($selectedGroup->workers as $worker) {
                    $labels[] = $worker->last_name . ' ' . mb_substr($worker->first_name, 0, 1) . '.';
                }
            }
        }
        
        return view('chart', compact('groups', 'selectedGroup', 'selectedGroupId', 'bVec', 'decisions', 'labels'));
    }
    
    public function importWorkers(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:txt,lst|max:1024'
        ]);
        
        $file = $request->file('file');
        $content = file_get_contents($file->path());
        $content = mb_convert_encoding($content, 'UTF-8', 'auto');
        $lines = explode("\n", $content);
        $added = 0;
        $duplicates = 0;
        
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            $parts = explode(';', $line);
            if (count($parts) >= 6) {
                $exists = Worker::where('last_name', trim($parts[0]))
                    ->where('first_name', trim($parts[1]))
                    ->where(function($q) use ($parts) {
                        $q->where('patronymic', isset($parts[2]) ? trim($parts[2]) : null)
                          ->orWhereNull('patronymic');
                    })
                    ->exists();
                
                if (!$exists) {
                    Worker::create([
                        'last_name' => trim($parts[0]),
                        'first_name' => trim($parts[1]),
                        'patronymic' => isset($parts[2]) ? trim($parts[2]) : null,
                        'age' => (int)trim($parts[3]),
                        'experience' => (int)trim($parts[4]),
                        'gender' => (int)trim($parts[5])
                    ]);
                    $added++;
                } else {
                    $duplicates++;
                }
            }
        }
        
        $message = "Импортировано: $added новых рабочих";
        if ($duplicates > 0) {
            $message .= ", пропущено дубликатов: $duplicates";
        }
        
        return redirect()->route('home', ['tab' => 'workers'])->with('success', $message);
    }
}