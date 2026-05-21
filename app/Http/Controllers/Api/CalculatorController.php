<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkGroup;
use App\Services\ProductivitySolver;

class CalculatorController extends Controller
{
    public function calculate($groupId)
    {
        $group = WorkGroup::with('workers')
            ->where('user_id', auth()->id())
            ->find($groupId);
        
        if (!$group) {
            return response()->json(['error' => 'Группа не найдена'], 404);
        }
        
        $solver = new ProductivitySolver($group);
        $decisions = $solver->calculateProductivity();
        
        $results = [];
        foreach ($group->workers as $i => $worker) {
            $results[] = [
                'id' => $worker->id,
                'name' => $worker->full_name,
                'productivity' => $decisions[$i] ?? 0
            ];
        }
        
        return response()->json([
            'success' => true,
            'group' => [
                'id' => $group->id,
                'name' => $group->name,
                'workers_count' => $group->workers->count()
            ],
            'results' => $results
        ]);
    }
}