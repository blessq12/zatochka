<?php

namespace App\Application\Workshop\WorkAttachment;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Application\Workshop\Port\EquipmentComponentBelongingPort;
use App\Domain\Workshop\VO\WorkTarget;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RepairWorkAttachmentStrategy implements WorkAttachmentStrategy
{
    public function __construct(
        private EquipmentComponentBelongingPort $equipmentComponents,
    ) {}

    public function resolveTarget(
        OrderProductionContextDTO $context,
        ?int $orderItemId,
        ?int $equipmentComponentId,
    ): WorkTarget {
        if ($equipmentComponentId === null) {
            throw new DomainException('Repair work must be linked to an equipment component.');
        }

        if ($orderItemId !== null) {
            throw new DomainException('Repair works must be linked to an equipment component, not an order item.');
        }

        $componentId = new EntityId($equipmentComponentId);

        foreach ($context->items as $item) {
            $equipmentId = $item->clientEquipmentId;

            if ($equipmentId === null) {
                continue;
            }

            if ($item->fullyRejected) {
                continue;
            }

            if (! $this->equipmentComponents->belongsToEquipment($equipmentId, $equipmentComponentId)) {
                continue;
            }

            return new WorkTarget(new EntityId($item->orderItemId), $componentId);
        }

        throw new DomainException('Equipment component does not belong to this repair order.');
    }
}
