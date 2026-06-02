<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\WorkGroup;
use App\Models\GroupProductivity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ExportController extends Controller
{
    public function exportWorkers()
    {
        try {
            $workers = Worker::where('user_id', auth()->id())->get();
            $content = '';
            foreach ($workers as $w) {
                $lastName = str_replace(';', '\;', $w->last_name);
                $firstName = str_replace(';', '\;', $w->first_name);
                $patronymic = str_replace(';', '\;', $w->patronymic ?? '');

                $content .= "{$lastName};{$firstName};{$patronymic};{$w->age};{$w->experience};{$w->gender}\n";
            }
            return response($content)
                ->header('Content-Type', 'text/plain; charset=UTF-8')
                ->header('Content-Disposition', 'attachment; filename="workers.lst"');
        } catch (\Exception $e) {
            Log::error('Ошибка экспорта: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при экспорте данных');
        }
    }

    public function exportStatisticsCsv(Request $request, $groupId)
    {
        $group = WorkGroup::where('id', $groupId)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $from = $request->get('from');
        $to = $request->get('to');

        $query = GroupProductivity::where('work_group_id', $groupId)
            ->where('user_id', Auth::id());

        if ($from) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to) {
            $query->whereDate('created_at', '<=', $to);
        }

        $productivities = $query->with('worker')->orderBy('created_at', 'desc')->get();

        $filename = "report_{$group->name}_" . ($from ?? 'all') . "_to_" . ($to ?? 'all') . ".csv";
        $filename = preg_replace('/[^a-z0-9_\-]/i', '_', $filename);

        return response()->stream(
            function() use ($productivities) {
                $handle = fopen('php://output', 'w');
                
                fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($handle, [
                    'ID', 'Рабочий', 'Объём (шт)', 'Время (ч)', 
                    'Производительность (шт/ч)', 'Дата'
                ], ';');

                foreach ($productivities as $p) {
                    fputcsv($handle, [
                        $p->worker_id,
                        $p->worker->full_name ?? 'Неизвестно',
                        $p->volume ?? 0,
                        $p->time ?? 0,
                        $p->value ?? 0,
                        $p->created_at->format('Y-m-d H:i:s')
                    ], ';');
                }
                
                fclose($handle);
            },
            200,
            [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ]
        );
    }
}