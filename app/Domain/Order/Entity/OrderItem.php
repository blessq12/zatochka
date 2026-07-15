<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\VO\OrderItemStatus;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class OrderItem
{
    private OrderItemStatus $status;
    private ?ReceptionData $receptionData = null;
    private ?EntityId $productionTaskId = null;
    private ?EntityId $itemPriceId = null;
    private ?EntityId $warrantyId = null;

    public function __construct(
        private readonly EntityId $id,
        private readonly EntityId $clientEquipmentId,
        OrderItemStatus $status = OrderItemStatus::Accepted,
    ) {
        $this->status = $status;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function clientEquipmentId(): EntityId
    {
        return $this->clientEquipmentId;
    }

    public function status(): OrderItemStatus
    {
        return $this->status;
    }

    public function receptionData(): ?ReceptionData
    {
        return $this->receptionData;
    }

    public function productionTaskId(): ?EntityId
    {
        return $this->productionTaskId;
    }

    public function itemPriceId(): ?EntityId
    {
        return $this->itemPriceId;
    }

    public function warrantyId(): ?EntityId
    {
        return $this->warrantyId;
    }

    public function completeReception(ReceptionData $receptionData): void
    {
        if ($this->receptionData !== null) {
            throw new DomainException('Reception data is already set for this order item.');
        }

        $this->receptionData = $receptionData;
    }

    public function bindProductionTask(EntityId $productionTaskId): void
    {
        if ($this->productionTaskId !== null) {
            throw new DomainException('Production task is already bound to this order item.');
        }

        $this->productionTaskId = $productionTaskId;
        $this->status = OrderItemStatus::InProduction;
    }

    public function bindItemPrice(EntityId $itemPriceId): void
    {
        $this->itemPriceId = $itemPriceId;
    }

    public function bindWarranty(EntityId $warrantyId): void
    {
        $this->warrantyId = $warrantyId;
    }

    public function markCompleted(): void
    {
        if ($this->status !== OrderItemStatus::InProduction) {
            throw new DomainException('Only items in production can be completed.');
        }

        $this->status = OrderItemStatus::Completed;
    }

    public function markRejected(): void
    {
        if ($this->status === OrderItemStatus::Issued) {
            throw new DomainException('Issued item cannot be rejected.');
        }

        $this->status = OrderItemStatus::Rejected;
    }

    public function markIssued(): void
    {
        if (! in_array($this->status, [OrderItemStatus::Completed, OrderItemStatus::Accepted], true)) {
            throw new DomainException('Item cannot be issued in current status.');
        }

        $this->status = OrderItemStatus::Issued;
    }

    public function hasReception(): bool
    {
        return $this->receptionData !== null;
    }
}
