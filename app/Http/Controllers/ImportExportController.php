<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportExportController extends Controller
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

    public function importWorkers(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:txt,lst,text/plain|max:1024'
            ]);

            $file = $request->file('file');
            $content = file_get_contents($file->path());

            $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1251'], true);
            if ($encoding && $encoding !== 'UTF-8') {
                $content = mb_convert_encoding($content, 'UTF-8', $encoding);
            }

            $importService = app(ImportService::class);
            $result = $importService->importFromContent($content, auth()->id());

            $message = "Импортировано: {$result['added']} новых рабочих";
            if ($result['duplicates'] > 0) {
                $message .= ", пропущено дубликатов: {$result['duplicates']}";
            }

            if (count($result['errors']) > 0) {
                return redirect()->route('home', ['tab' => 'workers'])
                    ->with('success', $message)
                    ->with('import_errors', $result['errors']);
            }

            return redirect()->route('home', ['tab' => 'workers'])
                ->with('success', $message);
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errors = $e->errors();
            $errorMessage = isset($errors['file'][0]) ? $errors['file'][0] : 'Файл должен быть в формате .lst или .txt';
            return redirect()->back()
                ->withErrors(['file' => $errorMessage])
                ->withInput();
                
        } catch (\Exception $e) {
            Log::error('Ошибка импорта: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Ошибка при импорте файла: ' . $e->getMessage());
        }
    }
}