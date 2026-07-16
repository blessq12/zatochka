<?php

namespace App\Application\Workshop\WorkAttachment;

use App\Domain\Equipment\Repository\ClientEquipmentRepository;
use App\Domain\Order\Entity\Order;
use App\Domain\Workshop\VO\WorkTarget;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RepairWorkAttachmentStrategy implements WorkAttachmentStrategy
{
    public function __construct(
        private ClientEquipmentRepository $equipment,
    ) {}

    public function resolveTarget(
        Order $order,
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

        foreach ($order->items() as $item) {
            $equipmentId = $item->clientEquipmentId();

            if ($equipmentId === null) {
                continue;
            }

            if ($item->isFullyRejected()) {
                continue;
            }

            $equipment = $this->equipment->findById($equipmentId);

            if ($equipment === null) {
                continue;
            }

            $component = $equipment->findComponent($componentId);

            if ($component === null) {
                continue;
            }

            return new WorkTarget($item->id(), $componentId);
        }

        throw new DomainException('Equipment component does not belong to this repair order.');
    }
}
