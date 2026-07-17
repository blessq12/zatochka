<?php

namespace App\Infrastructure\Pricing\Port;

use App\Application\Pricing\Port\OrderPricingGatePort;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderStatus;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentOrderPricingGatePort implements OrderPricingGatePort
{
    public function __construct(
        private OrderRepository $orders,
    ) {}

    public function assertAwaitingPricing(string $orderId): void
    {
        $order = $this->orders->getById(new OrderId($orderId));

        if ($order->status() !== OrderStatus::WorksCompleted) {
            throw new DomainException('Prices can only be changed while order is awaiting pricing.');
        }
    }

    public function assertItemPricable(string $orderId, int $orderItemId): void
    {
        $order = $this->orders->getById(new OrderId($orderId));
        $item = $order->item(new EntityId($orderItemId));

        if ($item->isFullyRejected()) {
            throw new DomainException('Cannot set price for a fully rejected order item.');
        }
    }
}
