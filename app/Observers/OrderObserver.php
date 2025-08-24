<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\TelegramService;
// use App\Services\SMSService;
use App\Services\BonusService;

class OrderObserver
{
    private TelegramService $telegramService;
    // private SMSService $smsService;
    private BonusService $bonusService;

    public function __construct(TelegramService $telegramService, BonusService $bonusService)
    {
        $this->telegramService = $telegramService;
        // $this->smsService = $smsService;
        $this->bonusService = $bonusService;
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        // Отправляем подтверждение заказа
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
     * Отправка подтверждения заказа
     */
    private function sendOrderConfirmation(Order $order): void
    {
        $message = "✅ Заявка {$order->order_number} создана!\n";
        $message .= "💰 Сумма: {$order->total_amount} ₽\n";
        $message .= "📋 Статус: {$order->status}";

        $this->createNotification($order, 'order_confirmation', $message);
        $this->sendNotifications($order, $message);
    }

    /**
     * Отправка уведомления об изменении статуса
     */
    private function sendStatusUpdate(Order $order): void
    {
        $statusText = $this->getStatusText($order->status);
        $message = "📋 Статус заявки {$order->order_number} изменен на: {$statusText}";

        $this->createNotification($order, 'status_update', $message);
        $this->sendNotifications($order, $message);
    }

    /**
     * Отправка уведомления о готовности
     */
    private function sendReadyNotification(Order $order): void
    {
        $message = "🎉 Заявка {$order->order_number} готова к выдаче!\n";
        $message .= "📍 Адрес: {$order->client->delivery_address}";

        $this->createNotification($order, 'ready', $message);
        $this->sendNotifications($order, $message);
    }

    /**
     * Отправка подтверждения оплаты
     */
    private function sendPaymentConfirmation(Order $order): void
    {
        $message = "💳 Оплата заявки {$order->order_number} подтверждена!\n";
        $message .= "💰 Сумма: {$order->total_amount} ₽";

        $this->createNotification($order, 'payment_required', $message);
        $this->sendNotifications($order, $message);
    }

    /**
     * Создание записи уведомления в БД
     */
    private function createNotification(Order $order, string $type, string $message): void
    {
        $order->notifications()->create([
            'client_id' => $order->client_id,
            'type' => $type,
            'message_text' => $message,
            'sent_at' => now()
        ]);
    }

    /**
     * Отправка уведомлений через сервисы
     */
    private function sendNotifications(Order $order, string $message): void
    {
        // Отправляем в Telegram
        if ($order->client->telegram) {
            $this->telegramService->sendMessage($order->client->telegram, $message);
        }

        // SMS отключен - используем только Telegram
        // if ($order->client->phone) {
        //     $this->smsService->sendSMS($order->client->phone, $message);
        // }
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
            default => $status
        };
    }

    /**
     * Начисление бонусов за заказ
     */
    private function awardBonusForOrder(Order $order): void
    {
        $this->bonusService->awardBonusForOrder($order);
    }
}
