<?php

namespace App\Domain\Order\AggregateRoot;

use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Event\OrderStatusChanged;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;

class OrderAggregateRoot extends AggregateRoot
{
    public function createOrder(string $orderId, string $clientId, string $orderNumber, array $orderData): self
    {
        $this->recordThat(new OrderCreated(
            orderId: $orderId,
            clientId: $clientId,
            orderNumber: $orderNumber,
            orderData: $orderData
        ));

        return $this;
    }

    public function changeStatus(string $orderId, string $oldStatus, string $newStatus, ?string $changedBy = null): self
    {
        $this->recordThat(new OrderStatusChanged(
            orderId: $orderId,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            changedBy: $changedBy
        ));

        return $this;
    }
}
