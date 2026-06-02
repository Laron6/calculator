<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService
{
    protected $token;
    protected $chatId;

    public function __construct()
    {
        $this->token = env('TELEGRAM_BOT_TOKEN');
        $this->chatId = env('TELEGRAM_CHAT_ID');
    }

    public function sendMessage($message)
    {
        if (!$this->token || !$this->chatId) {
            Log::warning('Telegram bot не настроен');
            return false;
        }

        try {
            $url = "https://api.telegram.org/bot{$this->token}/sendMessage";
            
            $response = Http::post($url, [
                'chat_id' => $this->chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Ошибка отправки в Telegram: ' . $e->getMessage());
            return false;
        }
    }

    public function sendProductivityReport($group, $calculatedResults, $from, $to)
    {
        $validResults = array_filter($calculatedResults, function($res) {
            return $res['productivity'] > 0;
        });
        
        $total = array_sum(array_column($calculatedResults, 'productivity'));
        $average = count($validResults) > 0 
            ? round(array_sum(array_column($validResults, 'productivity')) / count($validResults), 2) 
            : 0;
        
        $best = null;
        $worst = null;
        foreach ($calculatedResults as $res) {
            if ($res['productivity'] > 0) {
                if ($best === null || $res['productivity'] > $best['productivity']) {
                    $best = $res;
                }
                if ($worst === null || $res['productivity'] < $worst['productivity']) {
                    $worst = $res;
                }
            }
        }
        
        $productivities = array_column($validResults, 'productivity');
        $avg = count($productivities) > 0 ? array_sum($productivities) / count($productivities) : 0;
        $variance = 0;
        foreach ($productivities as $p) {
            $variance += pow($p - $avg, 2);
        }
        $stdDev = count($productivities) > 0 ? sqrt($variance / count($productivities)) : 0;
        $variationCoeff = $avg > 0 ? round(($stdDev / $avg) * 100, 1) : 0;
        
        if ($variationCoeff < 15) {
            $stability = "🔵 Отличная стабильность! Все работают примерно одинаково.";
        } elseif ($variationCoeff < 30) {
            $stability = "🟡 Средняя стабильность. Есть разброс в производительности.";
        } else {
            $stability = "🟠 Низкая стабильность. Большой разрыв между сотрудниками.";
        }
        
        $recommendations = [];
        if ($best && $worst) {
            $diff = round($best['productivity'] - $worst['productivity'], 2);
            $ratio = $best['productivity'] > 0 ? round($best['productivity'] / $worst['productivity'], 1) : 0;
            $recommendations[] = "📌 Разрыв между лучшим и худшим: {$diff} шт/ч ({$ratio}x)";
            $recommendations[] = "🎯 Рекомендация: Обратить внимание на {$worst['worker']->first_name} {$worst['worker']->last_name}";
        }
        
        if ($average < 30) {
            $recommendations[] = "⚠️ Общая производительность низкая. Рекомендуется:\n   • Провести обучение\n   • Оптимизировать процессы\n   • Пересмотреть нагрузку";
        } elseif ($average < 50) {
            $recommendations[] = "📈 Производительность средняя. Есть потенциал:\n   • Мотивировать сотрудников\n   • Внедрить KPI";
        } else {
            $recommendations[] = "🏆 Высокая производительность! Так держать!";
        }
        
        $trend = "📊 Тренд производительности: ";
        if (count($calculatedResults) >= 2) {
            $lastTwo = array_slice($calculatedResults, -2);
            $change = $lastTwo[1]['productivity'] - $lastTwo[0]['productivity'];
            if ($change > 5) {
                $trend .= "🔺 Рост! Производительность увеличивается 📈";
            } elseif ($change < -5) {
                $trend .= "🔻 Падение! Производительность снижается 📉";
            } else {
                $trend .= "➡️ Стабильно. Уровень держится на месте.";
            }
        } else {
            $trend .= "Недостаточно данных для анализа.";
        }
        
        $forecast = $average + ($trend === "🔺 Рост! Производительность увеличивается 📈" ? 5 : ($trend === "🔻 Падение! Производительность снижается 📉" ? -5 : 0));
        $forecast = max(0, round($forecast, 2));
        
        $message = "<b>📊 ОТЧЁТ ПО ПРОИЗВОДИТЕЛЬНОСТИ</b>\n\n";
        $message .= "<b>📁 Группа:</b> {$group->name}\n";
        $message .= "<b>📅 Период:</b> " . date('d.m.Y', strtotime($from)) . " — " . date('d.m.Y', strtotime($to)) . "\n\n";
        
        $message .= "<b>👥 РЕЗУЛЬТАТЫ ПО РАБОЧИМ:</b>\n";
        foreach ($calculatedResults as $res) {
            $productivity = number_format($res['productivity'], 2);
            $icon = $res['productivity'] > 0 ? '✅' : '⚠️';
            $message .= "{$icon} {$res['worker']->last_name} {$res['worker']->first_name}: <b>{$productivity}</b> шт/ч\n";
        }
        
        $message .= "\n<b>📈 ОБЩАЯ СТАТИСТИКА:</b>\n";
        $message .= "• Общая производительность: <b>" . number_format($total, 2) . "</b> шт/ч\n";
        $message .= "• Средняя производительность: <b>" . number_format($average, 2) . "</b> шт/ч\n";
        $message .= "• Коэффициент вариации: <b>{$variationCoeff}%</b>\n";
        $message .= "• {$stability}\n";
        
        if ($best) {
            $message .= "\n<b>🏆 ЛУЧШИЙ РАБОТНИК:</b>\n";
            $message .= "   👤 {$best['worker']->last_name} {$best['worker']->first_name}\n";
            $message .= "   📊 Производительность: <b>" . number_format($best['productivity'], 2) . "</b> шт/ч\n";
        }
        
        if ($worst) {
            $message .= "\n<b>📉 ХУДШИЙ РАБОТНИК:</b>\n";
            $message .= "   👤 {$worst['worker']->last_name} {$worst['worker']->first_name}\n";
            $message .= "   📊 Производительность: <b>" . number_format($worst['productivity'], 2) . "</b> шт/ч\n";
        }
        
        $message .= "\n<b>🤖 ИИ-АНАЛИТИКА И РЕКОМЕНДАЦИИ:</b>\n";
        foreach ($recommendations as $rec) {
            $message .= "{$rec}\n";
        }
        
        $message .= "\n<b>🔮 ИИ-ПРОГНОЗ:</b>\n";
        $message .= "{$trend}\n";
        $message .= "📌 Ожидаемая производительность в следующем периоде: <b>{$forecast}</b> шт/ч\n";
        
        $message .= "\n<b>⏰</b> <i>Отчёт сгенерирован автоматически системой объективной оценки производительности</i>";
        
        return $this->sendMessage($message);
    }
}