<?php

namespace App\Services\Messenger;

use App\Contracts\MessengerServiceInterface;
use Illuminate\Support\Facades\Log;

class TelegramMessengerService implements MessengerServiceInterface
{
    public function send(string $recipientId, string $text, array $options = []): void
    {
        $botToken = config('services.telegram.bot_token');

        if (!$botToken) {
            Log::warning('Telegram bot token not configured');
            return;
        }

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $data = [
            'chat_id' => (int) $recipientId,
            'text' => $text,
            'parse_mode' => 'HTML',
        ];

        if ($options['with_keyboard'] ?? false) {
            $data['reply_markup'] = json_encode($this->getMainKeyboard());
        }

        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode !== 200) {
                Log::error('Telegram send message failed', [
                    'http_code' => $httpCode,
                    'response' => $response,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Telegram send message exception: ' . $e->getMessage());
        }
    }

    private function getMainKeyboard(): array
    {
        return [
            'keyboard' => [
                [['text' => '👤 Аккаунт']],
                [
                    ['text' => '📋 Активные заказы'],
                    ['text' => '📚 История заказов'],
                ],
            ],
            'resize_keyboard' => true,
            'one_time_keyboard' => false,
        ];
    }
}
