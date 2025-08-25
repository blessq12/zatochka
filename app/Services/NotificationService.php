<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    /**
     * Создать уведомление
     */
    public function createNotification(array $data): Notification
    {
        return Notification::create([
            'client_id' => $data['client_id'],
            'order_id' => $data['order_id'] ?? null,
            'type' => $data['type'],
            'title' => $data['title'],
            'message' => $data['message'],
            'is_read' => false,
        ]);
    }

    /**
     * Создать уведомление о новом заказе
     */
    public function notifyOrderCreated(Order $order): void
    {
        $this->createNotification([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'type' => 'order_created',
            'title' => 'Заявка создана',
            'message' => "Ваша заявка №{$order->order_number} успешно создана. Мы свяжемся с вами в ближайшее время.",
        ]);
    }

    /**
     * Создать уведомление об изменении статуса заказа
     */
    public function notifyOrderStatusChanged(Order $order, string $oldStatus, string $newStatus): void
    {
        $statusMessages = [
            'in_progress' => 'Ваш заказ взят в работу',
            'completed' => 'Ваш заказ готов к выдаче',
            'cancelled' => 'Ваш заказ отменен',
        ];

        $message = $statusMessages[$newStatus] ?? "Статус заказа изменен на: {$newStatus}";

        $this->createNotification([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'type' => 'status_changed',
            'title' => 'Статус заказа изменен',
            'message' => $message,
        ]);
    }

    /**
     * Создать уведомление о готовности к выдаче
     */
    public function notifyOrderReady(Order $order): void
    {
        $this->createNotification([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'type' => 'order_ready',
            'title' => 'Заказ готов',
            'message' => "Ваш заказ №{$order->order_number} готов к выдаче. Приходите забирать!",
        ]);
    }

    /**
     * Создать уведомление о подтверждении заказа
     */
    public function notifyOrderConfirmed(Order $order): void
    {
        $this->createNotification([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'type' => 'order_confirmed',
            'title' => 'Заказ подтвержден',
            'message' => "Ваш заказ №{$order->order_number} подтвержден. Стоимость: {$order->total_amount} ₽",
        ]);
    }

    /**
     * Получить непрочитанные уведомления клиента
     */
    public function getUnreadNotifications(Client $client, int $limit = 10)
    {
        return $client->notifications()
            ->where('is_read', false)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Отметить уведомление как прочитанное
     */
    public function markAsRead(Notification $notification): bool
    {
        return $notification->update(['is_read' => true]);
    }

    /**
     * Отметить все уведомления клиента как прочитанные
     */
    public function markAllAsRead(Client $client): int
    {
        return $client->notifications()
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }

    /**
     * Удалить старые уведомления
     */
    public function deleteOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->where('is_read', true)
            ->delete();
    }

    /**
     * Получить количество непрочитанных уведомлений
     */
    public function getUnreadCount(Client $client): int
    {
        return $client->notifications()
            ->where('is_read', false)
            ->count();
    }
}
