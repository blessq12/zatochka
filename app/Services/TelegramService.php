<?php

namespace App\Services;

use App\Contracts\TelegramServiceContract;
use App\Models\TelegramChat;
use App\Models\Client;
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

            return $response->successful();
        } catch (\Exception $e) {
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
     * Отправить уведомление о новом заказе
     */
    public function sendNewOrderNotification(string $chatId, array $orderData): bool
    {
        $message = "🆕 <b>Новый заказ!</b>\n\n";
        $message .= "📋 Номер: <b>{$orderData['order_number']}</b>\n";
        $message .= "👤 Клиент: <b>{$orderData['client_name']}</b>\n";
        $message .= "📞 Телефон: <b>{$orderData['client_phone']}</b>\n";
        $message .= "🔧 Тип услуги: <b>{$orderData['service_type']}</b>\n";
        $message .= "💰 Сумма: <b>{$orderData['total_amount']} ₽</b>\n";
        $message .= "📅 Создан: <b>{$orderData['created_at']}</b>";

        return $this->sendMessage($chatId, $message);
    }

    /**
     * Отправить уведомление об изменении статуса заказа
     */
    public function sendOrderStatusChangeNotification(string $chatId, array $orderData): bool
    {
        $message = "🔄 <b>Статус заказа изменен</b>\n\n";
        $message .= "📋 Номер: <b>{$orderData['order_number']}</b>\n";
        $message .= "👤 Клиент: <b>{$orderData['client_name']}</b>\n";
        $message .= "📞 Телефон: <b>{$orderData['client_phone']}</b>\n";
        $message .= "🔄 Статус: <b>{$orderData['old_status']}</b> → <b>{$orderData['new_status']}</b>\n";
        $message .= "📅 Изменен: <b>{$orderData['changed_at']}</b>";

        return $this->sendMessage($chatId, $message);
    }

    /**
     * Отправить уведомление о новом отзыве
     */
    public function sendNewReviewNotification(string $chatId, array $reviewData): bool
    {
        $message = "⭐ <b>Новый отзыв!</b>\n\n";
        $message .= "📋 Заказ: <b>{$reviewData['order_number']}</b>\n";
        $message .= "👤 Клиент: <b>{$reviewData['client_name']}</b>\n";
        $message .= "⭐ Рейтинг: <b>{$reviewData['rating']}/5</b>\n";
        $message .= "💬 Комментарий: <b>{$reviewData['comment']}</b>\n";
        $message .= "📅 Создан: <b>{$reviewData['created_at']}</b>";

        return $this->sendMessage($chatId, $message);
    }

    /**
     * Отправить уведомление об изменении статуса отзыва
     */
    public function sendReviewStatusChangeNotification(string $chatId, array $reviewData): bool
    {
        $message = "🔄 <b>Статус отзыва изменен</b>\n\n";
        $message .= "📋 Заказ: <b>{$reviewData['order_number']}</b>\n";
        $message .= "👤 Клиент: <b>{$reviewData['client_name']}</b>\n";
        $message .= "⭐ Рейтинг: <b>{$reviewData['rating']}/5</b>\n";
        $message .= "🔄 Статус: <b>{$reviewData['old_status']}</b> → <b>{$reviewData['new_status']}</b>\n";
        $message .= "📅 Изменен: <b>{$reviewData['changed_at']}</b>";

        return $this->sendMessage($chatId, $message);
    }

    /**
     * Получить chat_id по username
     */
    private function getChatIdByUsername(string $username): ?int
    {
        try {
            $username = ltrim($username, '@');
            $chat = TelegramChat::where('username', $username)->first();

            if (!$chat) {
                return null;
            }

            return $chat->chat_id;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Получить chat_id клиента по его ID
     */
    public function getClientChatId(int $clientId): ?int
    {
        try {
            $client = Client::find($clientId);

            if (!$client || !$client->telegram) {
                return null;
            }

            return $this->getChatIdByUsername($client->telegram);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Отправить сообщение клиенту по его ID
     */
    public function sendMessageToClient(int $clientId, string $message): bool
    {
        $chatId = $this->getClientChatId($clientId);

        if (!$chatId) {
            return false;
        }

        return $this->sendMessage($chatId, $message);
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
            return null;
        }
    }
}
