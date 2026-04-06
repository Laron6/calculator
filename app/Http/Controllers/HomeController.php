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
                        $solver = new ProductivitySolver($selectedGroup);
                        $bVec = $solver->getBVec();
                        $decisions = $solver->solveByGauss();
                        $calculatedMetrics = $solver->getMetrics();
                        
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
                        $solver = new ProductivitySolver($selectedGroup);
                        $alt = $solver->solveAlternative();
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
}