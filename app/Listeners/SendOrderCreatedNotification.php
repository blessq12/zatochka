<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use App\Models\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendOrderCreatedNotification
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderCreated $event): void
    {
        $order = $event->order;

        // Создаем уведомление для клиента
        Notification::create([
            'client_id' => $order->client_id,
            'order_id' => $order->id,
            'type' => 'order_confirmation',
            'message_text' => "Заказ {$order->order_number} успешно создан. Сумма: {$order->total_amount} ₽",
        ]);

        Log::info("Order created notification sent via Event/Listener", [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'client_id' => $order->client_id,
        ]);
    }
}
