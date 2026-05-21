<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Models\Worker;
use App\Services\ProductivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductivityController extends Controller
{
    protected $productivityService;
    
    public function __construct(ProductivityService $productivityService)
    {
        $this->productivityService = $productivityService;
    }
    
    public function saveProductivity(Request $request, $groupId)
    {
        $request->validate([
            'volumes' => 'array',
            'volumes.*' => 'nullable|numeric|min:0',
            'times' => 'array',
            'times.*' => 'nullable|numeric|min:0.1'
        ], [
            'volumes.*.numeric' => 'Объём должен быть числом',
            'times.*.numeric' => 'Время должно быть числом',
            'times.*.min' => 'Время должно быть больше нуля'
        ]);
        
        $group = WorkGroup::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->first();
        
        if (!$group) {
            abort(403, 'У вас нет прав на редактирование этой группы');
        }
        
        $this->productivityService->saveProductivities(
            $groupId, 
            $request->volumes ?? [], 
            $request->times ?? []
        );
        
        return redirect()->route('home', ['tab' => 'statistics', 'group_id' => $groupId])->with('success', 'Данные сохранены');
    }
    
    public function calculate($groupId)
    {
        $group = WorkGroup::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->first();
        
        if (!$group) {
            abort(403, 'У вас нет прав на просмотр этой группы');
        }
        
        return redirect()->route('home', [
            'tab' => 'statistics',
            'group_id' => $groupId,
            'calculated' => 1
        ]);
    }
}