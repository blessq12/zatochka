<?php

namespace App\Services;

use App\Contracts\TelegramServiceContract;
use App\Models\TelegramChat;
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
            $response = Http::timeout(10)->post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            if (!$response->successful()) {
                Log::error('Telegram API error', [
                    'chat_id' => $chatId,
                    'status' => $response->status(),
                    'response' => $response->json()
                ]);
                return false;
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Telegram message sending failed', [
                'chat_id' => $chatId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function sendOrderConfirmation(string $telegramUsername, string $orderNumber, float $amount): bool
    {
        $chatId = $this->getChatIdByUsername($telegramUsername);
        if (!$chatId) {
            return false;
        }

        $message = "✅ <b>Заявка подтверждена!</b>\n\n";
        $message .= "📋 Номер заявки: <b>{$orderNumber}</b>\n";
        $message .= "💰 Сумма: <b>{$amount} ₽</b>\n\n";
        $message .= "Спасибо за заказ! Мы свяжемся с вами в ближайшее время.";

        return $this->sendMessage($chatId, $message);
    }

    public function sendVerificationCode(string $telegramUsername, string $code): bool
    {
        $chatId = $this->getChatIdByUsername($telegramUsername);
        if (!$chatId) {
            return false;
        }

        $message = "🔐 <b>Код верификации для аккаунта Заточка ТСК</b>\n\n";
        $message .= "Ваш код: <b>{$code}</b>\n\n";
        $message .= "Код действителен 10 минут.\n";
        $message .= "Если вы не запрашивали верификацию, проигнорируйте это сообщение.";

        return $this->sendMessage($chatId, $message);
    }

    public function sendVerificationSuccess(string $telegramUsername): bool
    {
        $chatId = $this->getChatIdByUsername($telegramUsername);
        if (!$chatId) {
            return false;
        }

        $message = "✅ <b>Telegram успешно верифицирован!</b>\n\n";
        $message .= "Ваш аккаунт в системе Заточка ТСК теперь подтвержден.\n";
        $message .= "Вы можете использовать все функции приложения.";

        return $this->sendMessage($chatId, $message);
    }

    /**
     * Получить chat_id по username
     */
    private function getChatIdByUsername(string $username): ?int
    {
        try {
            // Убираем @ если есть
            $username = ltrim($username, '@');

            // Ищем в базе данных
            $chat = TelegramChat::where('username', $username)->first();

            if (!$chat) {
                Log::warning('Telegram chat not found', [
                    'username' => $username
                ]);
                return null;
            }

            return $chat->chat_id;
        } catch (\Exception $e) {
            Log::error('Error getting chat_id by username', [
                'username' => $username,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    /**
     * Проверить доступность бота
     */
    public function checkBotHealth(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->apiUrl}/getMe");
            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Telegram bot health check failed', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Получить информацию о боте
     */
    public function getBotInfo(): ?array
    {
        try {
            $response = Http::timeout(5)->get("{$this->apiUrl}/getMe");

            if ($response->successful()) {
                return $response->json();
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get bot info', [
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
