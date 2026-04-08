<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Services\ProductivityService;
use App\Services\ProductivitySolver;
use Illuminate\Http\Request;

class ProductivityController extends Controller
{
    protected $productivityService;
    
    public function __construct(ProductivityService $productivityService)
    {
        $this->productivityService = $productivityService;
    }
    
    public function saveProductivity(Request $request, $groupId)
    {
        $min = config('productivity.min_productivity', 100);
        $max = config('productivity.max_productivity', 10000);
        
        $request->validate([
            'productivities' => 'array',
            'productivities.*' => "nullable|numeric|min:{$min}|max:{$max}"
        ], [
            'productivities.*.min' => 'Производительность должна быть не менее ' . $min,
            'productivities.*.max' => 'Производительность не должна превышать ' . $max
        ]);
        
        $this->productivityService->saveProductivities($groupId, $request->productivities);
        return redirect()->route('home', ['tab' => 'statistics', 'group_id' => $groupId])->with('success', 'Данные сохранены');
    }
    
    public function calculate($groupId)
    {
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
    }
    
    public function calculateAlternative($groupId)
    {
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
    }
}