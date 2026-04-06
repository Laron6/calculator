<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportExportController extends Controller
{
    public function exportWorkers()
    {
        try {
            $workers = Worker::all();
            $content = '';
            foreach ($workers as $w) {
                $content .= "{$w->last_name};{$w->first_name};{$w->patronymic};{$w->age};{$w->experience};{$w->gender}\n";
            }
            return response($content)
                ->header('Content-Type', 'text/plain')
                ->header('Content-Disposition', 'attachment; filename="workers.lst"');
        } catch (\Exception $e) {
            Log::error('Ошибка экспорта: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при экспорте данных');
        }
    }
    
    public function importWorkers(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:txt,lst|max:1024'
            ]);
            
            $file = $request->file('file');
            $content = file_get_contents($file->path());
            $content = mb_convert_encoding($content, 'UTF-8', 'auto');
            $lines = explode("\n", $content);
            $added = 0;
            $duplicates = 0;
            
            foreach ($lines as $line) {
                $line = trim($line);
                if (empty($line)) continue;
                
                $parts = explode(';', $line);
                if (count($parts) >= 6) {
                    $exists = Worker::where('last_name', trim($parts[0]))
                        ->where('first_name', trim($parts[1]))
                        ->where(function($q) use ($parts) {
                            $q->where('patronymic', isset($parts[2]) ? trim($parts[2]) : null)
                              ->orWhereNull('patronymic');
                        })
                        ->exists();
                    
                    if (!$exists) {
                        Worker::create([
                            'last_name' => trim($parts[0]),
                            'first_name' => trim($parts[1]),
                            'patronymic' => isset($parts[2]) ? trim($parts[2]) : null,
                            'age' => (int)trim($parts[3]),
                            'experience' => (int)trim($parts[4]),
                            'gender' => (int)trim($parts[5])
                        ]);
                        $added++;
                    } else {
                        $duplicates++;
                    }
                }
            }
            
            $message = "Импортировано: $added новых рабочих";
            if ($duplicates > 0) {
                $message .= ", пропущено дубликатов: $duplicates";
            }
            
            return redirect()->route('home', ['tab' => 'workers'])->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Ошибка импорта: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ошибка при импорте файла');
        }
    }
}