<?php

namespace App\Application\Order\ServiceType;

use App\Application\Equipment\DTO\EquipmentPartDTO;
use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Application\Order\Port\EquipmentProvisioningPort;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Order\Entity\OrderItem;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RepairOrderItemBuildStrategy implements OrderItemBuildStrategy
{
    public function __construct(
        private EntityIdGenerator $ids,
        private EquipmentProvisioningPort $equipment,
    ) {}

    public function buildItem(CreateOrderItemDTO $itemDto, int $clientId): OrderItem
    {
        $orderItemId = $itemDto->orderItemId ?? $this->ids->next('order_item')->value;
        $equipmentId = $itemDto->clientEquipmentId;

        if ($itemDto->isNewEquipment()) {
            $equipmentId = $this->ids->next('equipment')->value;
            $parts = [];

            foreach ($itemDto->equipmentParts as $part) {
                if (! filled($part['name'] ?? null)) {
                    continue;
                }

                $parts[] = new EquipmentPartDTO(
                    $this->ids->next('equipment_component')->value,
                    (string) $part['name'],
                    filled($part['serialNumber'] ?? null) ? (string) $part['serialNumber'] : null,
                );
            }

            $this->equipment->register(
                $equipmentId,
                $clientId,
                (string) $itemDto->equipmentTitle,
                (string) $itemDto->equipmentBrand,
                (string) $itemDto->equipmentModelName,
                $itemDto->equipmentNotes,
                $parts,
            );
        }

        if ($equipmentId === null) {
            throw new DomainException('Repair item requires client equipment.');
        }

        return OrderItem::forEquipment(
            new EntityId($orderItemId),
            new EntityId($equipmentId),
        );
    }
}
