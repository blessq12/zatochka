<?php

namespace App\Application\Order\ServiceType;

use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Order\Entity\OrderItem;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class RepairOrderItemBuildStrategy implements OrderItemBuildStrategy
{
    public function __construct(
        private EntityIdGenerator $ids,
    ) {}

    public function buildItem(CreateOrderItemDTO $itemDto): OrderItem
    {
        $orderItemId = $itemDto->orderItemId ?? $this->ids->next('order_item')->value;
        $equipmentId = $itemDto->clientEquipmentId;

        if ($equipmentId === null) {
            throw new DomainException('Repair item requires client equipment.');
        }

        return OrderItem::forEquipment(
            new EntityId($orderItemId),
            new EntityId($equipmentId),
        );
    }
}
