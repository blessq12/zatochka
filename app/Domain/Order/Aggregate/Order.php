<?php

namespace App\Domain\Order\Aggregate;

use App\Domain\Order\BillingType;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\OrderStatus;
use App\Domain\Order\Urgency;
use App\Shared\Domain\DomainException;

final class Order
{
    /**
     * @param  list<OrderItem>  $items
     */
    public function __construct(
        private ?int $id,
        private int $clientId,
        private BillingType $billingType,
        private Urgency $urgency,
        private OrderStatus $status,
        private ?int $masterId,
        private string $estimatedCost,
        private bool $needsDelivery,
        private ?string $deliveryAddress,
        private array $items,
    ) {
        if ($items === []) {
            throw new DomainException('Order must have at least one item.');
        }
        $this->assertDelivery();
        $this->assertEstimatedCost($estimatedCost);
    }

    /**
     * @param  list<OrderItem>  $items
     */
    public static function create(
        int $clientId,
        BillingType $billingType,
        Urgency $urgency,
        string $estimatedCost,
        bool $needsDelivery,
        ?string $deliveryAddress,
        array $items,
    ): self {
        if ($clientId < 1) {
            throw new DomainException('client_id is required.');
        }

        return new self(
            null,
            $clientId,
            $billingType,
            $urgency,
            OrderStatus::Created,
            null,
            $estimatedCost,
            $needsDelivery,
            $deliveryAddress,
            $items,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function clientId(): int
    {
        return $this->clientId;
    }

    public function billingType(): BillingType
    {
        return $this->billingType;
    }

    public function urgency(): Urgency
    {
        return $this->urgency;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function masterId(): ?int
    {
        return $this->masterId;
    }

    public function estimatedCost(): string
    {
        return $this->estimatedCost;
    }

    public function needsDelivery(): bool
    {
        return $this->needsDelivery;
    }

    public function deliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    /**
     * @return list<OrderItem>
     */
    public function items(): array
    {
        return $this->items;
    }

    /**
     * @param  list<OrderItem>  $items
     */
    public function replaceItems(array $items): void
    {
        if (! $this->status->allowsItemEdit()) {
            throw new DomainException('Order items can be edited only in created status.');
        }
        if ($items === []) {
            throw new DomainException('Order must have at least one item.');
        }
        $this->items = $items;
    }

    public function assignMaster(int $masterId): void
    {
        if ($masterId < 1) {
            throw new DomainException('master_id is required.');
        }
        if ($this->status !== OrderStatus::Created) {
            throw new DomainException('Master can be assigned only from created status.');
        }

        $this->masterId = $masterId;
        $this->status = OrderStatus::MasterAssigned;
    }

    public function transitionTo(OrderStatus $target): void
    {
        if ($target === OrderStatus::MasterAssigned) {
            throw new DomainException('Use assign master to move to master_assigned.');
        }

        if ($target === OrderStatus::WorksCompleted) {
            throw new DomainException('This status is set by integration events.');
        }

        if ($target === OrderStatus::InProgress && $this->status !== OrderStatus::WaitingParts) {
            throw new DomainException('Cannot set in_progress from this status via transition.');
        }

        if (! $this->status->canTransitionTo($target)) {
            throw new DomainException(sprintf(
                'Cannot transition from %s to %s.',
                $this->status->value,
                $target->value,
            ));
        }

        $this->status = $target;
    }

    public function markAcceptedIntoWork(int $masterId): void
    {
        if ($this->status !== OrderStatus::MasterAssigned) {
            throw new DomainException('Order can be accepted into work only from master_assigned.');
        }
        if ($this->masterId !== $masterId) {
            throw new DomainException('Order is assigned to another master.');
        }

        $this->status = OrderStatus::InProgress;
    }

    public function markWorksCompleted(): void
    {
        if ($this->status !== OrderStatus::InProgress) {
            throw new DomainException('Works can be completed only from in_progress.');
        }

        $this->status = OrderStatus::WorksCompleted;
    }

    private function assertEstimatedCost(string $estimatedCost): void
    {
        if (! is_numeric($estimatedCost) || (float) $estimatedCost < 0) {
            throw new DomainException('estimated_cost must be a non-negative number.');
        }
    }

    private function assertDelivery(): void
    {
        if ($this->needsDelivery && ($this->deliveryAddress === null || trim($this->deliveryAddress) === '')) {
            throw new DomainException('delivery_address is required when delivery is needed.');
        }
        if (! $this->needsDelivery) {
            $this->deliveryAddress = null;
        }
    }
}
