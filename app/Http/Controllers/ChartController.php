<?php

namespace App\Http\Controllers;

use App\Services\GroupService;
use App\Services\ProductivitySolver;
use App\Models\WorkGroup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChartController extends Controller
{
    protected $groupService;
    
    public function __construct(GroupService $groupService)
    {
        $this->groupService = $groupService;
    }
    
    public function charts(Request $request)
    {
        try {
            $groups = $this->groupService->getAll();
            $selectedGroupId = $request->get('group_id');
            $selectedGroup = null;
            $productivities = [];
            $labels = [];
            
            if ($selectedGroupId) {
                $selectedGroup = WorkGroup::with('workers')->find($selectedGroupId);
                if ($selectedGroup && $selectedGroup->workers->count() > 0) {
                    $solver = new ProductivitySolver($selectedGroup);
                    $productivities = $solver->calculateProductivity();
                    
                    foreach ($selectedGroup->workers as $worker) {
                        $labels[] = $worker->last_name . ' ' . mb_substr($worker->first_name, 0, 1) . '.';
                    }
                }
            }
            
            return view('pages.chart', compact('groups', 'selectedGroup', 'selectedGroupId', 'productivities', 'labels'));
        } catch (\Exception $e) {
            Log::error('Ошибка в charts: ' . $e->getMessage());
            return view('pages.chart')->with('error', 'Ошибка при загрузке графиков');
        }
    }
}