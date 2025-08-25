<?php

namespace App\Listeners\Order;

use App\Services\TelegramService;
use App\Services\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class HandleOrderCreated implements ShouldQueue
{
    protected TelegramService $telegramService;
    protected NotificationService $notificationService;

    /**
     * Create the event listener.
     */
    public function __construct(TelegramService $telegramService, NotificationService $notificationService)
    {
        $this->telegramService = $telegramService;
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        $order = $event->order;

        Log::info('Order created event dispatched', ['order_id' => $order->id]);

        $this->notificationService->notifyOrderCreated($order);

        if ($order->client && $order->client->telegram && $order->client->isTelegramVerified()) {
            $orderData = [
                'order_number' => $order->order_number,
                'client_name' => $order->client->full_name,
                'client_phone' => $order->client->phone,
                'service_type' => $order->service_type,
                'total_amount' => $order->total_amount,
                'created_at' => $order->created_at->format('d.m.Y H:i'),
            ];

            $this->telegramService->sendNewOrderNotification(
                $this->telegramService->getClientChatId($order->client_id),
                $orderData
            );
        }
    }
}
