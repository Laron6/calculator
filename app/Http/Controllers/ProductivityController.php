<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Services\ProductivityService;
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
        $request->validate([
            'volumes' => 'array',
            'volumes.*' => 'nullable|numeric|min:0',
            'times' => 'array',
            'times.*' => 'nullable|numeric|min:0'
        ], [
            'volumes.*.numeric' => 'Объём должен быть числом',
            'times.*.numeric' => 'Время должно быть числом'
        ]);
        
        $this->productivityService->saveProductivities(
            $groupId, 
            $request->volumes ?? [], 
            $request->times ?? []
        );
        
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
}