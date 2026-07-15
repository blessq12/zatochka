<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\SharpeningToolType;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class OrderItem
{
    private OrderItemStatus $status;
    private ?ReceptionData $receptionData = null;
    private ?EntityId $productionTaskId = null;
    private ?EntityId $itemPriceId = null;
    private ?EntityId $warrantyId = null;
    private ?string $toolName;
    private ?SharpeningToolType $toolType;
    private ?int $quantity;

    public function __construct(
        private readonly EntityId $id,
        private readonly ?EntityId $clientEquipmentId = null,
        ?string $toolName = null,
        ?SharpeningToolType $toolType = null,
        ?int $quantity = null,
        OrderItemStatus $status = OrderItemStatus::Accepted,
    ) {
        $normalizedTool = $toolName !== null ? trim($toolName) : null;
        $hasTool = $normalizedTool !== null && $normalizedTool !== '';
        $hasEquipment = $clientEquipmentId !== null;

        if ($hasEquipment === $hasTool) {
            throw new DomainException('Order item must reference either equipment or a tool name.');
        }

        if ($hasTool) {
            if ($toolType === null) {
                throw new DomainException('Sharpening tool type is required.');
            }

            if ($quantity === null || $quantity < 1) {
                throw new DomainException('Sharpening tool quantity must be at least 1.');
            }
        } elseif ($toolType !== null || $quantity !== null) {
            throw new DomainException('Tool type and quantity are only allowed for sharpening items.');
        }

        $this->toolName = $hasTool ? $normalizedTool : null;
        $this->toolType = $hasTool ? $toolType : null;
        $this->quantity = $hasTool ? $quantity : null;
        $this->status = $status;
    }

    public static function forEquipment(EntityId $id, EntityId $clientEquipmentId): self
    {
        return new self($id, $clientEquipmentId);
    }

    public static function forTool(
        EntityId $id,
        string $toolName,
        SharpeningToolType $toolType,
        int $quantity,
    ): self {
        return new self($id, null, $toolName, $toolType, $quantity);
    }

    public static function reconstitute(
        EntityId $id,
        ?EntityId $clientEquipmentId,
        ?string $toolName,
        OrderItemStatus $status,
        ?ReceptionData $receptionData = null,
        ?EntityId $productionTaskId = null,
        ?EntityId $itemPriceId = null,
        ?EntityId $warrantyId = null,
        ?SharpeningToolType $toolType = null,
        ?int $quantity = null,
    ): self {
        $item = new self($id, $clientEquipmentId, $toolName, $toolType, $quantity, $status);
        $item->receptionData = $receptionData;
        $item->productionTaskId = $productionTaskId;
        $item->itemPriceId = $itemPriceId;
        $item->warrantyId = $warrantyId;

        return $item;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function clientEquipmentId(): ?EntityId
    {
        return $this->clientEquipmentId;
    }

    public function toolName(): ?string
    {
        return $this->toolName;
    }

    public function toolType(): ?SharpeningToolType
    {
        return $this->toolType;
    }

    public function quantity(): ?int
    {
        return $this->quantity;
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
