<?php

namespace App\Application\Workshop\WorkAttachment;

use App\Domain\Order\Entity\Order;
use App\Domain\Workshop\VO\WorkTarget;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class SharpeningWorkAttachmentStrategy implements WorkAttachmentStrategy
{
    public function resolveTarget(
        Order $order,
        ?int $orderItemId,
        ?int $equipmentComponentId,
    ): WorkTarget {
        if ($equipmentComponentId !== null) {
            throw new DomainException('Sharpening works must be linked to an order item, not an equipment component.');
        }

        if ($orderItemId === null) {
            throw new DomainException('Sharpening work must be linked to an order item.');
        }

        $orderItem = null;

        foreach ($order->items() as $item) {
            if ($item->id()->value === $orderItemId) {
                $orderItem = $item;

                break;
            }
        }

        if ($orderItem === null) {
            throw new DomainException('Work must be linked to an order item.');
        }

        if ($orderItem->clientEquipmentId() !== null) {
            throw new DomainException('Sharpening work cannot target an equipment order item.');
        }

        if ($orderItem->isFullyRejected()) {
            throw new DomainException('Cannot add work to a fully rejected order item.');
        }

        return new WorkTarget(new EntityId($orderItemId));
    }
}
