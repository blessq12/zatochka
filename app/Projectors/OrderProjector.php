<?php

namespace App\Projectors;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Event\OrderStatusChanged;
use App\Models\Order;
use Spatie\EventSourcing\EventHandlers\Projectors\Projector;

class OrderProjector extends Projector
{
    public function onOrderCreated(OrderCreated $event): void
    {
        Order::create([
            'id' => $event->orderId,
            'client_id' => $event->clientId,
            'order_number' => $event->orderNumber,
            ...$event->orderData
        ]);
    }

    public function onOrderStatusChanged(OrderStatusChanged $event): void
    {
        Order::where('id', $event->orderId)
            ->update(['status' => $event->newStatus]);
    }
}
