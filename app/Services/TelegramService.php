<?php

namespace App\Services;

use App\Contracts\TelegramServiceContract;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TelegramService implements TelegramServiceContract
{
    private string $botToken;
    private string $apiUrl;

    public function __construct()
    {
        $this->botToken = config('services.telegram.bot_token');
        $this->apiUrl = "https://api.telegram.org/bot{$this->botToken}";
    }

    public function sendMessage(string $chatId, string $message): bool
    {
        try {
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram message sending failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    public function sendOrderConfirmation(string $telegramUsername, string $orderNumber, float $amount): bool
    {
        $message = "✅ <b>Заявка подтверждена!</b>\n\n";
        $message .= "📋 Номер заявки: <b>{$orderNumber}</b>\n";
        $message .= "💰 Сумма: <b>{$amount} ₽</b>\n\n";
        $message .= "Спасибо за заказ! Мы свяжемся с вами в ближайшее время.";

        return $this->sendMessage($telegramUsername, $message);
    }
}
