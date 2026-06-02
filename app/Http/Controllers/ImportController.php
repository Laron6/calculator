<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Services\ImportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ImportController extends Controller
{
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