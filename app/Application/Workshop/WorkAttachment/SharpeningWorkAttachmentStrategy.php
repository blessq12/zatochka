<?php

namespace App\Application\Workshop\WorkAttachment;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Domain\Workshop\VO\WorkTarget;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class SharpeningWorkAttachmentStrategy implements WorkAttachmentStrategy
{
    public function resolveTarget(
        OrderProductionContextDTO $context,
        ?int $orderItemId,
        ?int $equipmentComponentId,
    ): WorkTarget {
        if ($equipmentComponentId !== null) {
            throw new DomainException('Sharpening works must be linked to an order item, not an equipment component.');
        }

        if ($orderItemId === null) {
            throw new DomainException('Sharpening work must be linked to an order item.');
        }

        $orderItem = $context->item($orderItemId);

        if ($orderItem === null) {
            throw new DomainException('Work must be linked to an order item.');
        }

        if ($orderItem->clientEquipmentId !== null) {
            throw new DomainException('Sharpening work cannot target an equipment order item.');
        }

        if ($orderItem->fullyRejected) {
            throw new DomainException('Cannot add work to a fully rejected order item.');
        }

        return new WorkTarget(new EntityId($orderItemId));
    }
}
