<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TelegramChat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TelegramController extends Controller
{
    /**
     * Обработка вебхука от Telegram
     */
    public function webhook(Request $request): JsonResponse
    {
        try {
            $update = $request->all();

            Log::info('Telegram webhook received', ['update' => $update]);

            // Обрабатываем только сообщения
            if (!isset($update['message'])) {
                return response()->json(['ok' => true]);
            }

            $message = $update['message'];
            $chatId = $message['chat']['id'] ?? null;
            $username = $message['chat']['username'] ?? null;
            $firstName = $message['chat']['first_name'] ?? null;
            $text = $message['text'] ?? null;

            if (!$chatId) {
                return response()->json(['ok' => true, 'error' => 'No chat_id']);
            }

            // Находим или создаем чат
            $telegramChat = TelegramChat::byChatId($chatId)->first();

            if (!$telegramChat) {
                $telegramChat = TelegramChat::create([
                    'chat_id' => $chatId,
                    'username' => $username ?? '',
                    'metadata' => [
                        'first_name' => $firstName,
                        'last_name' => $message['chat']['last_name'] ?? null,
                    ],
                    'is_active' => true,
                ]);
            }

            // Обрабатываем все текстовые сообщения и команды одинаково
            if ($text) {
                $this->handleMessage($telegramChat, $text, $chatId);
            }

            return response()->json(['ok' => true]);
        } catch (\Exception $e) {
            Log::error('Telegram webhook error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['ok' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Обработка всех сообщений (команды и текст)
     */
    private function handleMessage(TelegramChat $chat, string $text, int $chatId): void
    {
        $botToken = config('services.telegram.bot_token');
        $responseMessage = "я не знаю че делать, чуть позже пойму";
        
        $this->sendMessage($botToken, $chatId, $responseMessage);
    }

    /**
     * Отправка сообщения через Telegram Bot API
     */
    private function sendMessage(string $botToken, int $chatId, string $text): void
    {
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

        $data = [
            'chat_id' => $chatId,
            'text' => $text,
        ];

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
}
