<?php

namespace App\Domain\OrderFulfillment\Entity;

use App\Domain\OrderFulfillment\Enum\OrderSource;
use App\Domain\OrderFulfillment\Enum\OrderStatus;
use App\Domain\OrderFulfillment\Enum\OrderUrgency;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Domain\OrderFulfillment\ValueObject\OrderNumber;
use DateTimeImmutable;

final class Order
{
    /**
     * @param  list<string>  $serviceTypes
     * @param  list<OrderWork>  $works
     * @param  list<OrderTool>  $tools
     * @param  list<OrderMaterial>  $materials
     */
    public function __construct(
        private ?int $id,
        private OrderNumber $orderNumber,
        private OrderStatus $status,
        private array $serviceTypes,
        private ?OrderUrgency $urgency,
        private bool $isWarranty,
        private bool $needsDelivery,
        private ?string $deliveryAddress,
        private ?string $problemDescription,
        private ?string $internalNotes,
        private ?string $price,
        private OrderSource $source,
        private ?ClientSnapshot $clientSnapshot,
        private ?int $leadId,
        private ?int $clientId,
        private ?int $equipmentId,
        private ?int $masterId,
        private int $branchId,
        private ?int $warrantyParentOrderId,
        private ?DateTimeImmutable $takenAt,
        private ?DateTimeImmutable $readyAt,
        private ?DateTimeImmutable $issuedAt,
        private array $works = [],
        private array $tools = [],
        private array $materials = [],
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function orderNumber(): OrderNumber
    {
        return $this->orderNumber;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    /** @return list<string> */
    public function serviceTypes(): array
    {
        return $this->serviceTypes;
    }

    public function urgency(): ?OrderUrgency
    {
        return $this->urgency;
    }

    public function isWarranty(): bool
    {
        return $this->isWarranty;
    }

    public function needsDelivery(): bool
    {
        return $this->needsDelivery;
    }

    public function deliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function problemDescription(): ?string
    {
        return $this->problemDescription;
    }

    public function internalNotes(): ?string
    {
        return $this->internalNotes;
    }

    public function price(): ?string
    {
        return $this->price;
    }

    public function source(): OrderSource
    {
        return $this->source;
    }

    public function clientSnapshot(): ?ClientSnapshot
    {
        return $this->clientSnapshot;
    }

    public function leadId(): ?int
    {
        return $this->leadId;
    }

    public function clientId(): ?int
    {
        return $this->clientId;
    }

    public function equipmentId(): ?int
    {
        return $this->equipmentId;
    }

    public function masterId(): ?int
    {
        return $this->masterId;
    }

    public function branchId(): int
    {
        return $this->branchId;
    }

    public function warrantyParentOrderId(): ?int
    {
        return $this->warrantyParentOrderId;
    }

    public function takenAt(): ?DateTimeImmutable
    {
        return $this->takenAt;
    }

    public function readyAt(): ?DateTimeImmutable
    {
        return $this->readyAt;
    }

    public function issuedAt(): ?DateTimeImmutable
    {
        return $this->issuedAt;
    }

    /** @return list<OrderWork> */
    public function works(): array
    {
        return $this->works;
    }

    /** @return list<OrderTool> */
    public function tools(): array
    {
        return $this->tools;
    }

    /** @return list<OrderMaterial> */
    public function materials(): array
    {
        return $this->materials;
    }

    public function clientDisplayName(): string
    {
        return $this->clientSnapshot?->fullName() ?? '';
    }

    public function clientDisplayPhone(): string
    {
        return $this->clientSnapshot?->phone() ?? '';
    }

    public function isActive(): bool
    {
        return ! in_array($this->status, [OrderStatus::Issued, OrderStatus::Cancelled], true);
    }

    public function assignId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }
}
