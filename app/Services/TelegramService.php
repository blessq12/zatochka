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
            $response = Http::post("{$this->apiUrl}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML'
            ]);

            if (!$response->successful()) {
                Log::error('Telegram message sending failed', [
                    'chat_id' => $chatId,
                    'response' => $response->json(),
                    'status' => $response->status()
                ]);
                return false;
            }

            return true;
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
        $chatId = $this->getChatIdByUsername($telegramUsername);
        if (!$chatId) {
            Log::error('Chat ID not found for username', ['username' => $telegramUsername]);
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
        Log::info('Attempting to send verification code', [
            'username' => $telegramUsername,
            'code' => $code
        ]);

        $chatId = $this->getChatIdByUsername($telegramUsername);
        if (!$chatId) {
            Log::error('Chat ID not found for username', ['username' => $telegramUsername]);
            return false;
        }

        Log::info('Found chat ID for username', [
            'username' => $telegramUsername,
            'chat_id' => $chatId
        ]);

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
            Log::error('Chat ID not found for username', ['username' => $telegramUsername]);
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
        // Убираем @ если есть
        $username = ltrim($username, '@');
        
        Log::info('Looking for chat by username', ['username' => $username]);
        
        // Ищем в базе данных
        $chat = TelegramChat::where('username', $username)->first();
        
        if ($chat) {
            Log::info('Chat found in database', [
                'username' => $username,
                'chat_id' => $chat->chat_id
            ]);
            return $chat->chat_id;
        }

        Log::warning('Chat not found in database', ['username' => $username]);
        return null;
    }
}
