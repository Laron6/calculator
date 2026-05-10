<?php

namespace App\Services;

use App\Models\Worker;
use Illuminate\Support\Facades\Validator;

class ImportService
{
    public function importFromContent(string $content): array
    {
        $lines = explode("\n", $content);

        $result = [
            'added' => 0,
            'duplicates' => 0,
            'errors' => [],
        ];

        foreach ($lines as $lineNumber => $line) {
            $line = trim($line);
            if (empty($line)) continue;

            $parts = explode(';', $line);

            if (count($parts) < 6) {
                $result['errors'][] = "Строка " . ($lineNumber + 1) . ": неверный формат (ожидается 6 полей, найдено " . count($parts) . ")";
                continue;
            }

            $data = [
                'last_name' => trim($parts[0]),
                'first_name' => trim($parts[1]),
                'patronymic' => isset($parts[2]) ? trim($parts[2]) : null,
                'age' => (int) trim($parts[3]),
                'experience' => (int) trim($parts[4]),
                'gender' => (int) trim($parts[5]),
            ];

            $validator = Validator::make($data, [
                'last_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁ-]+$/u',
                'first_name' => 'required|string|max:50|regex:/^[а-яА-ЯёЁ-]+$/u',
                'patronymic' => 'nullable|string|max:50|regex:/^[а-яА-ЯёЁ-]*$/u',
                'age' => 'required|integer|min:18|max:100',
                'experience' => 'required|integer|min:0|max:80',
                'gender' => 'required|in:0,1',
            ]);

            if ($validator->fails()) {
                $errors = implode(', ', $validator->errors()->all());
                $result['errors'][] = "Строка " . ($lineNumber + 1) . " ({$data['last_name']} {$data['first_name']}): {$errors}";
                continue;
            }

            if ($data['experience'] > ($data['age'] - 18)) {
                $result['errors'][] = "Строка " . ($lineNumber + 1) . " ({$data['last_name']} {$data['first_name']}): стаж (" . $data['experience'] . ") не может быть больше возраста минус 18 лет";
                continue;
            }

            $exists = Worker::where('last_name', $data['last_name'])
                ->where('first_name', $data['first_name'])
                ->where(function ($q) use ($data) {
                    $q->where('patronymic', $data['patronymic'])
                      ->orWhereNull('patronymic');
                })
                ->exists();

            if ($exists) {
                $result['duplicates']++;
                continue;
            }

            Worker::create($data);
            $result['added']++;
        }

        return $result;
    }
}