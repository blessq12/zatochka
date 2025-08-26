<?php

namespace App\Listeners\Order;

use App\Services\TelegramService;
use App\Services\NotificationService;
use App\Services\BonusService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleOrderStatusChanged implements ShouldQueue
{
    protected TelegramService $telegramService;
    protected NotificationService $notificationService;
    protected BonusService $bonusService;

    /**
     * Create the event listener.
     */
    public function __construct(TelegramService $telegramService, NotificationService $notificationService, BonusService $bonusService)
    {
        $this->telegramService = $telegramService;
        $this->notificationService = $notificationService;
        $this->bonusService = $bonusService;
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $order = $event->order;
        $oldStatus = $event->oldStatus;
        $newStatus = $event->newStatus;

        Log::info('Order status changed', [
            'order_id' => $order->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);

        // Создаем уведомление в системе
        $this->notificationService->notifyOrderStatusChanged($order, $oldStatus, $newStatus);

        // Начисляем бонусы при закрытии заказа
        if ($newStatus === 'closed' && $order->client) {
            $this->bonusService->awardBonusForOrder($order);
        }

        // Отправляем уведомление в Telegram клиенту
        if ($order->client && $order->client->telegram && $order->client->isTelegramVerified()) {
            $orderData = [
                'order_number' => $order->order_number,
                'client_name' => $order->client->full_name,
                'client_phone' => $order->client->phone,
                'old_status' => $this->getStatusDisplayName($oldStatus),
                'new_status' => $this->getStatusDisplayName($newStatus),
                'changed_at' => now()->format('d.m.Y H:i'),
            ];

            $this->telegramService->sendOrderStatusChangeNotification(
                $this->telegramService->getClientChatId($order->client_id),
                $orderData
            );
        }
    }

    /**
     * Получить отображаемое имя статуса
     */
    private function getStatusDisplayName(string $status): string
    {
        return match ($status) {
            'new' => 'Новый',
            'confirmed' => 'Подтвержден',
            'courier_pickup' => 'Передан курьеру (забор)',
            'master_received' => 'Передан мастеру',
            'in_progress' => 'В работе',
            'work_completed' => 'Работа завершена',
            'courier_delivery' => 'Передан курьеру (доставка)',
            'ready_for_pickup' => 'Готов к выдаче',
            'delivered' => 'Доставлен',
            'payment_received' => 'Оплачен',
            'closed' => 'Закрыт',
            'cancelled' => 'Отменен',
            default => $status,
        };
    }
}
