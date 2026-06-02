<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Services\WorkerService;
use App\Services\GroupService;
use App\Services\ProductivityService;
use App\Services\ProductivitySolver;
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
            $volumes = [];
            $times = [];
            $recordDates = [];
            $calculatedResults = [];
            $showResults = $request->get('calculated') == 1;
            $showNoDataWarning = false;
            $currentDate = now()->format('Y-m-d');
            
            // Установка дат по умолчанию для фильтра (последние 3 месяца)
            if (!$request->has('from') && !$request->has('to')) {
                $from = now()->subMonths(3)->format('Y-m-d');
                $to = now()->format('Y-m-d');
            } else {
                $from = $request->get('from');
                $to = $request->get('to');
            }
            
            if ($selectedGroupId) {
                $selectedGroup = WorkGroup::with('workers')->find($selectedGroupId);
                if ($selectedGroup) {
                    $volumes = $this->productivityService->getVolumes($selectedGroupId, $from, $to);
                    $times = $this->productivityService->getTimes($selectedGroupId, $from, $to);
                    $recordDates = $this->productivityService->getRecordDates($selectedGroupId, $from, $to);
                    
                    if (($from || $to) && !$this->productivityService->hasDataForPeriod($selectedGroupId, $from, $to)) {
                        $showNoDataWarning = true;
                    }
                    
                    if ($showResults) {
                        $solver = new ProductivitySolver($selectedGroup);
                        $decisions = $solver->calculateProductivity();
                        
                        Log::info('Расчёт производительности', [
                            'group' => $selectedGroup->name,
                            'workers_count' => count($selectedGroup->workers),
                            'decisions' => $decisions,
                            'from' => $from,
                            'to' => $to
                        ]);
                        
                        $workerArray = $selectedGroup->workers->values();
                        foreach ($workerArray as $i => $worker) {
                            $calculatedResults[] = [
                                'worker' => $worker,
                                'productivity' => $decisions[$i] ?? 0
                            ];
                        }
                    }
                }
            }
            
            $tab = $request->get('tab', 'workers');
            
            if ($tab === 'statistics') {
                return view('statistics.index', compact(
                    'workers', 'groups', 'selectedGroup', 'selectedGroupId',
                    'volumes', 'times', 'recordDates', 'calculatedResults', 
                    'showResults', 'from', 'to', 'showNoDataWarning', 'currentDate'
                ));
            }
            
            return view('workers.index', compact(
                'workers', 'groups', 'selectedGroup', 'selectedGroupId',
                'volumes', 'times', 'calculatedResults', 'showResults'
            ));
        } catch (\Exception $e) {
            Log::error('Ошибка в index: ' . $e->getMessage());
            return view('workers.index')->with('error', 'Произошла ошибка при загрузке данных');
        }
    }
}