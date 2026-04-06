<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Services\ProductivityService;
use App\Services\ProductivitySolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductivityController extends Controller
{
    protected $productivityService;
    
    public function __construct(ProductivityService $productivityService)
    {
        $this->productivityService = $productivityService;
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
}