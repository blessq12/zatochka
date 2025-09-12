<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\Enum\OrderStatus;
use App\Domain\Order\Enum\OrderType;
use App\Domain\Order\Enum\OrderUrgency;

class Order
{
    public function __construct(
        private ?int $id,
        private int $clientId,
        private int $branchId,
        private int $managerId,
        private ?int $masterId,
        private string $orderNumber,
        private OrderType $type,
        private OrderStatus $status,
        private OrderUrgency $urgency = OrderUrgency::NORMAL,
        private bool $isPaid = false,
        private ?\DateTime $paidAt = null,
        private ?int $discountId = null,
        private ?float $totalAmount = null,
        private ?float $finalPrice = null,
        private ?float $costPrice = null,
        private ?float $profit = null,
        private bool $isDeleted = false,
        private ?\DateTime $createdAt = null,
        private ?\DateTime $updatedAt = null,
    ) {}

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClientId(): int
    {
        return $this->clientId;
    }

    public function getBranchId(): int
    {
        return $this->branchId;
    }

    public function getManagerId(): int
    {
        return $this->managerId;
    }

    public function getMasterId(): ?int
    {
        return $this->masterId;
    }

    public function getOrderNumber(): string
    {
        return $this->orderNumber;
    }

    public function getType(): OrderType
    {
        return $this->type;
    }

    public function getStatus(): OrderStatus
    {
        return $this->status;
    }

    public function getUrgency(): string
    {
        return $this->urgency;
    }

    public function isPaid(): bool
    {
        return $this->isPaid;
    }

    public function getPaidAt(): ?\DateTime
    {
        return $this->paidAt;
    }

    public function getDiscountId(): ?int
    {
        return $this->discountId;
    }

    public function getTotalAmount(): ?float
    {
        return $this->totalAmount;
    }

    public function getFinalPrice(): ?float
    {
        return $this->finalPrice;
    }

    public function getCostPrice(): ?float
    {
        return $this->costPrice;
    }

    public function getProfit(): ?float
    {
        return $this->profit;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    // Business methods
    public function isNew(): bool
    {
        return $this->status === OrderStatus::NEW;
    }

    public function isInWork(): bool
    {
        return $this->status === OrderStatus::IN_WORK;
    }

    public function isReady(): bool
    {
        return $this->status === OrderStatus::READY;
    }

    public function isIssued(): bool
    {
        return $this->status === OrderStatus::ISSUED;
    }

    public function isCancelled(): bool
    {
        return $this->status === OrderStatus::CANCELLED;
    }

    public function isFinal(): bool
    {
        return $this->status->isFinal();
    }

    public function isManagerStatus(): bool
    {
        return $this->status->isManagerStatus();
    }

    public function isWorkshopStatus(): bool
    {
        return $this->status->isWorkshopStatus();
    }

    // Mutators
    public function changeStatus(OrderStatus $newStatus): void
    {
        $this->status = $newStatus;
    }

    public function assignMaster(int $masterId): void
    {
        $this->masterId = $masterId;
    }

    public function markAsPaid(\DateTime $paidAt = null): void
    {
        $this->isPaid = true;
        $this->paidAt = $paidAt ?? new \DateTime();
    }

    public function setPricing(float $totalAmount, float $finalPrice, float $costPrice = null): void
    {
        $this->totalAmount = $totalAmount;
        $this->finalPrice = $finalPrice;
        $this->costPrice = $costPrice;

        if ($costPrice !== null) {
            $this->profit = $finalPrice - $costPrice;
        }
    }

    public function softDelete(): void
    {
        $this->isDeleted = true;
    }

    public function restore(): void
    {
        $this->isDeleted = false;
    }

    // Factory method
    public static function create(
        int $clientId,
        int $branchId,
        int $managerId,
        string $orderNumber,
        OrderType $type = OrderType::REPAIR,
        OrderStatus $status = OrderStatus::NEW,
        ?int $masterId = null,
        string $urgency = 'normal'
    ): self {
        return new self(
            id: null,
            clientId: $clientId,
            branchId: $branchId,
            managerId: $managerId,
            masterId: $masterId,
            orderNumber: $orderNumber,
            type: $type,
            status: $status,
            urgency: $urgency
        );
    }

    // Array conversion
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_id' => $this->clientId,
            'branch_id' => $this->branchId,
            'manager_id' => $this->managerId,
            'master_id' => $this->masterId,
            'order_number' => $this->orderNumber,
            'type' => $this->type->value,
            'status' => $this->status->value,
            'urgency' => $this->urgency,
            'is_paid' => $this->isPaid,
            'paid_at' => $this->paidAt?->format('Y-m-d H:i:s'),
            'discount_id' => $this->discountId,
            'total_amount' => $this->totalAmount,
            'final_price' => $this->finalPrice,
            'cost_price' => $this->costPrice,
            'profit' => $this->profit,
            'is_deleted' => $this->isDeleted,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt?->format('Y-m-d H:i:s'),
        ];
    }

    // Magic methods for Filament compatibility
    public function __get(string $name): mixed
    {
        return match ($name) {
            'id' => $this->id,
            'client_id' => $this->clientId,
            'branch_id' => $this->branchId,
            'manager_id' => $this->managerId,
            'master_id' => $this->masterId,
            'order_number' => $this->orderNumber,
            'type' => $this->type,
            'status' => $this->status,
            'urgency' => $this->urgency,
            'is_paid' => $this->isPaid,
            'paid_at' => $this->paidAt,
            'discount_id' => $this->discountId,
            'total_amount' => $this->totalAmount,
            'final_price' => $this->finalPrice,
            'cost_price' => $this->costPrice,
            'profit' => $this->profit,
            'is_deleted' => $this->isDeleted,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            default => throw new \InvalidArgumentException("Property {$name} does not exist"),
        };
    }

    public function __isset(string $name): bool
    {
        return in_array($name, [
            'id',
            'client_id',
            'branch_id',
            'manager_id',
            'master_id',
            'order_number',
            'type',
            'status',
            'urgency',
            'is_paid',
            'paid_at',
            'discount_id',
            'total_amount',
            'final_price',
            'cost_price',
            'profit',
            'is_deleted',
            'created_at',
            'updated_at'
        ]);
    }
}
