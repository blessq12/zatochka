<?php

namespace App\Infrastructure\Pricing\Port;

use App\Application\Pricing\Port\OrderPricingGatePort;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderStatus;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;

final readonly class EloquentOrderPricingGatePort implements OrderPricingGatePort
{
    public function assertAwaitingPricing(string $orderId): void
    {
        $status = OrderModel::query()->whereKey($orderId)->value('status');

        if ($status === null) {
            throw new DomainException('Order not found.');
        }

        if ($status !== OrderStatus::WorksCompleted->value) {
            throw new DomainException('Prices can only be changed while order is awaiting pricing.');
        }
    }

    public function assertItemPricable(string $orderId, int $orderItemId): void
    {
        $item = OrderItemModel::query()
            ->where('order_id', $orderId)
            ->whereKey($orderItemId)
            ->first(['id', 'quantity', 'rejected_quantity', 'status']);

        if ($item === null) {
            throw new DomainException('Order item not found.');
        }

        $quantity = (int) $item->quantity;
        $rejectedQuantity = (int) $item->rejected_quantity;
        $status = (string) $item->status;
        $repairableQuantity = $quantity > 0
            ? max(0, $quantity - $rejectedQuantity)
            : ($status === OrderItemStatus::Rejected->value ? 0 : 1);
        $fullyRejected = $status === OrderItemStatus::Rejected->value || $repairableQuantity === 0;

        if ($fullyRejected) {
            throw new DomainException('Cannot set price for a fully rejected order item.');
        }
    }
}
