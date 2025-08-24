<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\TelegramService;
use App\Services\BonusService;
use Illuminate\Support\Facades\Log;

class OrderObserver
{
    private TelegramService $telegramService;
    private BonusService $bonusService;

    public function __construct(TelegramService $telegramService, BonusService $bonusService)
    {
        $this->telegramService = $telegramService;
        $this->bonusService = $bonusService;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // 1. Проверяем наличие учетной записи клиента
        if (!$order->client) {
            Log::warning("Заявка {$order->order_number} создана без привязки к клиенту");
            return;
        }

        // 2. Проверяем наличие Telegram и его подтверждение
        if (!$this->validateClientTelegram($order->client)) {
            Log::warning("Клиент {$order->client->id} не имеет подтвержденного Telegram для заявки {$order->order_number}");
            return;
        }

        // 3. Отправляем сообщение с данными по заявке
        $this->sendOrderConfirmation($order);
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        // Проверяем изменения статуса
        if ($order->wasChanged('status')) {
            $this->sendStatusUpdate($order);
        }

        // Проверяем готовность к выдаче
        if ($order->wasChanged('is_ready_for_pickup') && $order->is_ready_for_pickup) {
            $this->sendReadyNotification($order);
        }

        // Проверяем оплату
        if ($order->wasChanged('is_paid') && $order->is_paid) {
            $this->sendPaymentConfirmation($order);
        }

        // Проверяем завершение заказа для начисления бонусов
        if ($order->wasChanged('status') && in_array($order->status, ['closed', 'payment_received'])) {
            $this->awardBonusForOrder($order);
        }
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Order $order): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Order $order): void
    {
        //
    }

    /**
     * Проверка наличия и подтверждения Telegram у клиента
     */
    private function validateClientTelegram($client): bool
    {
        return !empty($client->telegram) && $client->isTelegramVerified();
    }

    /**
     * Отправка подтверждения заказа с данными
     */
    private function sendOrderConfirmation(Order $order): void
    {
        $message = "✅ Заявка {$order->order_number} создана!\n\n";
        $message .= "📋 Тип услуги: {$order->service_type}\n";
        $message .= "🔧 Тип инструмента: {$order->tool_type}\n";
        $message .= "💰 Сумма: {$order->total_amount} ₽\n";
        $message .= "📝 Статус: {$this->getStatusText($order->status)}\n";

        if ($order->problem_description) {
            $message .= "❓ Проблема: {$order->problem_description}\n";
        }

        if ($order->needs_delivery) {
            $message .= "🚚 Доставка: {$order->delivery_address}\n";
        }

        $this->createNotification($order, 'order_confirmation', $message);
        $this->sendTelegramNotification($order, $message);
    }

    /**
     * Отправка уведомления об изменении статуса
     */
    private function sendStatusUpdate(Order $order): void
    {
        $statusText = $this->getStatusText($order->status);
        $message = "📋 Статус заявки {$order->order_number} изменен на: {$statusText}";

        $this->createNotification($order, 'status_update', $message);
        $this->sendTelegramNotification($order, $message);
    }

    /**
     * Отправка уведомления о готовности
     */
    private function sendReadyNotification(Order $order): void
    {
        $message = "🎉 Заявка {$order->order_number} готова к выдаче!\n";
        $message .= "📍 Адрес: {$order->client->delivery_address}";

        $this->createNotification($order, 'ready', $message);
        $this->sendTelegramNotification($order, $message);
    }

    /**
     * Отправка подтверждения оплаты
     */
    private function sendPaymentConfirmation(Order $order): void
    {
        $message = "💳 Оплата заявки {$order->order_number} подтверждена!\n";
        $message .= "💰 Сумма: {$order->total_amount} ₽";

        $this->createNotification($order, 'payment_confirmation', $message);
        $this->sendTelegramNotification($order, $message);
    }

    /**
     * Создание записи уведомления в БД
     */
    private function createNotification(Order $order, string $type, string $message): void
    {
        try {
            $order->notifications()->create([
                'client_id' => $order->client_id,
                'type' => $type,
                'message_text' => $message,
                'sent_at' => now()
            ]);

            Log::info("Уведомление создано", [
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'type' => $type,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error("Ошибка создания уведомления", [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Отправка уведомления в Telegram
     */
    private function sendTelegramNotification(Order $order, string $message): void
    {
        if (!$this->validateClientTelegram($order->client)) {
            Log::warning("Не удалось отправить Telegram уведомление - клиент не имеет подтвержденного Telegram", [
                'order_id' => $order->id,
                'client_id' => $order->client_id
            ]);
            return;
        }

        // Получаем chat_id через связь с TelegramChat
        $chatId = $this->getChatIdForClient($order->client);
        if (!$chatId) {
            Log::warning("Не удалось получить chat_id для клиента", [
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'telegram' => $order->client->telegram
            ]);
            return;
        }

        try {
            $this->telegramService->sendMessage($chatId, $message);

            Log::info("Telegram уведомление отправлено", [
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'telegram' => $order->client->telegram,
                'chat_id' => $chatId,
                'message' => $message
            ]);
        } catch (\Exception $e) {
            Log::error("Ошибка отправки Telegram уведомления", [
                'order_id' => $order->id,
                'client_id' => $order->client_id,
                'telegram' => $order->client->telegram,
                'chat_id' => $chatId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Получить chat_id для клиента через TelegramChat
     */
    private function getChatIdForClient($client): ?int
    {
        // Ищем чат по username клиента
        $chat = \App\Models\TelegramChat::where('username', $client->telegram)->first();

        return $chat?->chat_id;
    }

    /**
     * Получение текста статуса
     */
    private function getStatusText(string $status): string
    {
        return match ($status) {
            'new' => 'Новая',
            'in_progress' => 'В работе',
            'completed' => 'Завершена',
            'cancelled' => 'Отменена',
            'closed' => 'Закрыта',
            'payment_received' => 'Оплата получена',
            default => $status
        };
    }

    /**
     * Начисление бонусов за заказ
     */
    private function awardBonusForOrder(Order $order): void
    {
        try {
            $this->bonusService->awardBonusForOrder($order);
            Log::info("Бонусы начислены за заказ", ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error("Ошибка начисления бонусов", [
                'order_id' => $order->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
