<?php

namespace App\Http\Controllers;

use App\Models\WorkGroup;
use App\Services\ProductivityService;
use App\Services\ProductivitySolver;
use App\Services\TelegramService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramController extends Controller
{
    protected $productivityService;
    protected $telegramService;
    
    public function __construct(
        ProductivityService $productivityService,
        TelegramService $telegramService
    ) {
        $this->productivityService = $productivityService;
        $this->telegramService = $telegramService;
    }
    
    public function sendReport(Request $request, $groupId)
    {
        $group = WorkGroup::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->first();
        
        if (!$group) {
            return redirect()->back()->with('error', 'Группа не найдена');
        }
        
        $from = $request->get('from', now()->subMonths(3)->format('Y-m-d'));
        $to = $request->get('to', now()->format('Y-m-d'));
        
        $solver = new ProductivitySolver($group);
        $decisions = $solver->calculateProductivity();
        
        $calculatedResults = [];
        $workerArray = $group->workers->values();
        foreach ($workerArray as $i => $worker) {
            $calculatedResults[] = [
                'worker' => $worker,
                'productivity' => $decisions[$i] ?? 0
            ];
        }
        
        $success = $this->telegramService->sendProductivityReport($group, $calculatedResults, $from, $to);
        
        if ($success) {
            return redirect()->back()->with('success', 'Отчёт отправлен в Telegram!');
        } else {
            return redirect()->back()->with('error', 'Не удалось отправить отчёт. Проверьте настройки бота.');
        }
    }
}