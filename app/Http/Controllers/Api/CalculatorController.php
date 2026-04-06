<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WorkGroup;
use App\Services\ProductivitySolver;
use App\Http\Resources\ProductivityResource;
use App\Http\Resources\AlternativeResource;
use Illuminate\Http\Request;

class CalculatorController extends Controller
{
    public function calculate($groupId)
    {
        $group = WorkGroup::with('workers')->find($groupId);
        
        if (!$group) {
            return response()->json(['error' => 'Группа не найдена'], 404);
        }
        
        if ($group->workers->count() < 2) {
            return response()->json(['error' => 'Нужно минимум 2 рабочих'], 400);
        }
        
        $solver = new ProductivitySolver($group);
        $solver->solveByGauss();
        
        return response()->json(new ProductivityResource($group, $solver));
    }
    
    public function calculateAlternative($groupId)
    {
        $group = WorkGroup::with('workers')->find($groupId);
        
        if (!$group) {
            return response()->json(['error' => 'Группа не найдена'], 404);
        }
        
        if ($group->workers->count() < 3) {
            return response()->json(['error' => 'Нужно минимум 3 рабочих'], 400);
        }
        
        $solver = new ProductivitySolver($group);
        $result = $solver->solveAlternative();
        
        return response()->json(new AlternativeResource($group, $result));
    }
}