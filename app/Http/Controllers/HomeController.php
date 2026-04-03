<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use App\Services\WorkerService;
use App\Services\GroupService;
use App\Services\ProductivityService;
use App\Services\ProductivityCalculator;
use App\Http\Requests\WorkerRequest;
use App\Http\Requests\GroupRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected $workerService;
    protected $groupService;
    protected $productivityService;
    
    public function __construct(
        WorkerService $workerService,
        GroupService $groupService,
        ProductivityService $productivityService
    ) {
        $this->workerService = $workerService;
        $this->groupService = $groupService;
        $this->productivityService = $productivityService;
    }
    
    public function index(Request $request)
    {
        try {
            $workers = $this->workerService->getAll();
            $groups = $this->groupService->getAll();
            
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
                    $productivities = $this->productivityService->getProductivities($selectedGroupId);
                    
                    foreach ($selectedGroup->workers as $worker) {
                        $productivityValues[$worker->id] = $productivities[$worker->id]->value ?? '';
                    }
                    
                    if ($showResults) {
                        $calculator = new ProductivityCalculator($selectedGroup);
                        $bVec = $calculator->getBVec();
                        $decisions = $calculator->calculate();
                        $calculatedMetrics = $calculator->calculateMetrics();
                        
                        Log::info('=== КАЛЬКУЛЯТОР ===');
                        Log::info('Группа: ' . $selectedGroup->name);
                        Log::info('Кол-во рабочих: ' . count($selectedGroup->workers));
                        Log::info('BVec: ' . json_encode($bVec));
                        Log::info('Decisions: ' . json_encode($decisions));
                        
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
            
            return view('layouts.app', compact(
                'workers', 'groups', 'selectedGroup', 'selectedGroupId',
                'productivityValues', 'calculatedResults', 'calculatedMetrics',
                'showResults', 'showAlternative', 'alternativeResults', 'activeTab'
            ));
        } catch (\Exception $e) {
            Log::error('Ошибка в index: ' . $e->getMessage());
            return view('layouts.app')->with('error', 'Произошла ошибка при загрузке данных');
        }
    }
    
    public function addWorker(WorkerRequest $request)
    {
        try {
            $this->workerService->create($request->validated());
            return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Рабочий добавлен');
        } catch (\Exception $e) {
            Log::error('Ошибка добавления рабочего: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при добавлении рабочего');
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
        } catch (\Exception $e) {
            Log::error('Ошибка обновления рабочего: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при обновлении рабочего');
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
    
    public function createGroup(Request $request)
    {
        try {
            $request->validate(['name' => 'required|string|max:100|regex:/^[а-яА-ЯёЁa-zA-Z0-9\s-]+$/u']);
            $this->groupService->create($request->only('name'));
            return redirect()->route('home', ['tab' => 'workers'])->with('success', 'Группа создана');
        } catch (\Exception $e) {
            Log::error('Ошибка создания группы: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при создании группы');
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
    
    public function updateGroup(Request $request, $id)
    {
        try {
            $request->validate(['name' => 'required|string|max:100|regex:/^[а-яА-ЯёЁa-zA-Z0-9\s-]+$/u']);
            $group = WorkGroup::findOrFail($id);
            $this->groupService->update($group, $request->only('name'));
            return redirect()->route('home', ['tab' => 'workers', 'group_id' => $id])->with('success', 'Группа переименована');
        } catch (\Exception $e) {
            Log::error('Ошибка обновления группы: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при обновлении группы');
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
    
    public function saveProductivity(Request $request, $groupId)
    {
        try {
            $request->validate([
                'productivities' => 'array',
                'productivities.*' => 'nullable|numeric|min:0|max:999999'
            ]);
            
            $this->productivityService->saveProductivities($groupId, $request->productivities);
            return redirect()->route('home', ['tab' => 'statistics', 'group_id' => $groupId])->with('success', 'Данные сохранены');
        } catch (\Exception $e) {
            Log::error('Ошибка сохранения производительности: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при сохранении данных');
        }
    }
    
    public function calculate($groupId)
    {
        try {
            $group = WorkGroup::findOrFail($groupId);
            if ($group->workers->count() < 2) {
                return redirect()->route('home', [
                    'tab' => 'statistics',
                    'group_id' => $groupId
                ])->with('error', 'В группе должно быть минимум 2 рабочих для расчета');
            }
            
            return redirect()->route('home', [
                'tab' => 'statistics',
                'group_id' => $groupId,
                'calculated' => 1
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при расчете: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при расчете производительности');
        }
    }
    
    public function calculateAlternative($groupId)
    {
        try {
            $group = WorkGroup::findOrFail($groupId);
            if ($group->workers->count() < 3) {
                return redirect()->route('home', [
                    'tab' => 'statistics',
                    'group_id' => $groupId
                ])->with('error', 'В группе должно быть минимум 3 рабочих для альтернативного расчета');
            }
            
            return redirect()->route('home', [
                'tab' => 'statistics',
                'group_id' => $groupId,
                'calculated' => 1,
                'alternative' => 1
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка при альтернативном расчете: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при альтернативном расчете');
        }
    }
    
    public function exportWorkers()
    {
        try {
            $workers = Worker::all();
            $content = '';
            foreach ($workers as $w) {
                $content .= "{$w->last_name};{$w->first_name};{$w->patronymic};{$w->age};{$w->experience};{$w->gender}\n";
            }
            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="workers.lst"');
        } catch (\Exception $e) {
            Log::error('Ошибка экспорта: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при экспорте данных');
        }
    }
    
    public function charts(Request $request)
    {
        try {
            $groups = $this->groupService->getAll();
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
            
            return view('pages.chart', compact('groups', 'selectedGroup', 'selectedGroupId', 'bVec', 'decisions', 'labels'));
        } catch (\Exception $e) {
            Log::error('Ошибка в charts: ' . $e->getMessage());
            return view('pages.chart')->with('error', 'Ошибка при загрузке графиков');
        }
    }
    
    public function importWorkers(Request $request)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Ошибка импорта: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при импорте файла');
        }
    }
}