<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Services\ProductivityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        // Проверяем, что группа принадлежит текущему пользователю (ДО try)
        $group = WorkGroup::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->first();
        
        if (!$group) {
            abort(403, 'У вас нет прав на редактирование этой группы');
        }
        
        try {
            $request->validate([
                'volumes' => 'nullable|array',
                'times' => 'nullable|array',
                'record_dates' => 'nullable|array',
            ]);
            
            $recordDates = $request->record_dates ?? [];
            
            $result = $this->productivityService->saveProductivities(
                $groupId, 
                $request->volumes ?? [], 
                $request->times ?? [],
                $recordDates
            );
            
            Log::info('Результат сохранения', $result);
            
            if (!empty($result['errors'])) {
                return redirect()->route('home', [
                    'tab' => 'statistics', 
                    'group_id' => $groupId
                ])->with('warning', 'Некоторые данные не сохранены: ' . implode(', ', $result['errors']));
            }
            
            return redirect()->route('home', [
                'tab' => 'statistics', 
                'group_id' => $groupId
            ])->with('success', "Сохранено {$result['saved_count']} записей");
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Ошибка сохранения: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при сохранении: ' . $e->getMessage());
        }
    }
    
    public function calculate($groupId, Request $request)
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
            'calculated' => 1,
            'from' => $request->get('from'),
            'to' => $request->get('to')
        ]);
    }
}