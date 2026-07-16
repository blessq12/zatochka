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
    private ?EntityId $warrantyId = null;
    private ?string $toolName;
    private ?SharpeningToolType $toolType;
    private ?int $quantity;
    private int $rejectedQuantity = 0;
    private ?string $rejectionReason = null;

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
        ?EntityId $warrantyId = null,
        ?SharpeningToolType $toolType = null,
        ?int $quantity = null,
        int $rejectedQuantity = 0,
        ?string $rejectionReason = null,
    ): self {
        $item = new self($id, $clientEquipmentId, $toolName, $toolType, $quantity, $status);
        $item->receptionData = $receptionData;
        $item->warrantyId = $warrantyId;
        $item->rejectedQuantity = max(0, $rejectedQuantity);
        $item->rejectionReason = $rejectionReason;

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

    public function rejectedQuantity(): int
    {
        return $this->rejectedQuantity;
    }

    public function rejectionReason(): ?string
    {
        return $this->rejectionReason;
    }

    public function status(): OrderItemStatus
    {
        return $this->status;
    }

    public function receptionData(): ?ReceptionData
    {
        return $this->receptionData;
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

    public function bindWarranty(EntityId $warrantyId): void
    {
        $this->warrantyId = $warrantyId;
    }

    public function markInProduction(): void
    {
        if ($this->isFullyRejected()) {
            return;
        }

        if ($this->status === OrderItemStatus::Issued) {
            throw new DomainException('Issued item cannot enter production.');
        }

        if (in_array($this->status, [OrderItemStatus::Completed, OrderItemStatus::Rejected], true)) {
            return;
        }

        $this->status = OrderItemStatus::InProduction;
    }

    public function markCompleted(): void
    {
        if ($this->isFullyRejected()) {
            return;
        }

        if ($this->status === OrderItemStatus::Completed) {
            return;
        }

        if ($this->status !== OrderItemStatus::InProduction) {
            throw new DomainException('Only items in production can be completed.');
        }

        $this->status = OrderItemStatus::Completed;
    }

    /**
     * Partial reject for sharpening items (quantity > 0).
     *
     * @return int Newly rejected count
     */
    public function rejectUnits(int $count, string $reason): int
    {
        if ($this->quantity === null) {
            throw new DomainException('Partial reject is only allowed for sharpening items. Use markRejected() for equipment.');
        }

        if ($this->status === OrderItemStatus::Issued) {
            throw new DomainException('Issued item cannot be rejected.');
        }

        if ($count < 1) {
            throw new DomainException('Rejected quantity must be at least 1.');
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('Rejection reason is required.');
        }

        if ($this->rejectedQuantity + $count > $this->quantity) {
            throw new DomainException(sprintf(
                'Cannot reject %d units: only %d repairable left.',
                $count,
                $this->repairableQuantity(),
            ));
        }

        $this->rejectedQuantity += $count;
        $this->rejectionReason = $this->rejectionReason === null
            ? $reason
            : $this->rejectionReason."\n".$reason;

        if ($this->rejectedQuantity === $this->quantity) {
            $this->status = OrderItemStatus::Rejected;
        }

        return $count;
    }

    /**
     * Binary reject for equipment items (single unit).
     */
    public function markRejected(string $reason): void
    {
        if ($this->quantity !== null) {
            throw new DomainException('Use rejectUnits() for sharpening items.');
        }

        if ($this->status === OrderItemStatus::Issued) {
            throw new DomainException('Issued item cannot be rejected.');
        }

        if ($this->status === OrderItemStatus::Rejected) {
            return;
        }

        $reason = trim($reason);
        if ($reason === '') {
            throw new DomainException('Rejection reason is required.');
        }

        $this->rejectedQuantity = 1;
        $this->rejectionReason = $reason;
        $this->status = OrderItemStatus::Rejected;
    }

    public function markIssued(): void
    {
        if ($this->isFullyRejected()) {
            return;
        }

        if (! in_array($this->status, [OrderItemStatus::Completed, OrderItemStatus::Accepted], true)) {
            throw new DomainException('Item cannot be issued in current status.');
        }

        $this->status = OrderItemStatus::Issued;
    }

    public function hasReception(): bool
    {
        return $this->receptionData !== null;
    }

    public function repairableQuantity(): int
    {
        if ($this->quantity !== null) {
            return max(0, $this->quantity - $this->rejectedQuantity);
        }

        return $this->status === OrderItemStatus::Rejected ? 0 : 1;
    }

    public function isFullyRejected(): bool
    {
        return $this->status === OrderItemStatus::Rejected || $this->repairableQuantity() === 0;
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [
            OrderItemStatus::Completed,
            OrderItemStatus::Rejected,
            OrderItemStatus::Issued,
        ], true);
    }
}
