<?php

namespace App\Domain\Order\Entity;

use App\Domain\Order\Event\ClientAssigned;
use App\Domain\Order\Event\OrderCancelled;
use App\Domain\Order\Event\OrderClosed;
use App\Domain\Order\Event\OrderCreated;
use App\Domain\Order\Event\OrderIssued;
use App\Domain\Order\Event\OrderItemAdded;
use App\Domain\Order\Event\ReceptionCompleted;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class Order extends AggregateRoot
{
    private EntityId $clientId;
    private Money $estimatedCost;
    private OrderStatus $status;
    private readonly DateTimeImmutable $createdAt;

    /** @var array<int, OrderItem> */
    private array $items = [];

    /**
     * @param list<OrderItem> $items
     */
    private function __construct(
        private readonly EntityId $id,
        EntityId $clientId,
        Money $estimatedCost,
        DateTimeImmutable $createdAt,
        array $items,
        private readonly OrderServiceType $serviceType,
        private readonly OrderBillingType $billingType,
        private readonly OrderUrgency $urgency,
        private readonly bool $deliveryRequired,
        private readonly ?string $defects,
        private readonly ?string $internalNotes,
        private readonly ?EntityId $warrantySourceOrderId = null,
        OrderStatus $status = OrderStatus::Created,
    ) {
        if ($items === []) {
            throw new DomainException('Order must contain at least one item.');
        }

        $this->assertItemsMatchServiceType($items, $serviceType);

        $this->clientId = $clientId;
        $this->estimatedCost = $estimatedCost;
        $this->createdAt = $createdAt;
        $this->status = $status;

        foreach ($items as $item) {
            $this->items[$item->id()->value] = $item;
        }
    }

    /**
     * @param list<OrderItem> $items
     */
    public static function create(
        EntityId $id,
        EntityId $clientId,
        Money $estimatedCost,
        array $items,
        OrderServiceType $serviceType,
        OrderBillingType $billingType,
        OrderUrgency $urgency,
        bool $deliveryRequired = false,
        ?string $defects = null,
        ?string $internalNotes = null,
        ?EntityId $warrantySourceOrderId = null,
        ?DateTimeImmutable $createdAt = null,
    ): self {
        self::assertWarrantySource($billingType, $warrantySourceOrderId, $id);

        $createdAt ??= new DateTimeImmutable();
        $order = new self(
            $id,
            $clientId,
            $estimatedCost,
            $createdAt,
            $items,
            $serviceType,
            $billingType,
            $urgency,
            $deliveryRequired,
            self::normalizeText($defects),
            self::normalizeText($internalNotes),
            $warrantySourceOrderId,
        );

        $order->record(new OrderCreated($id, $clientId, $estimatedCost, $createdAt));
        $order->record(new ClientAssigned($id, $clientId));

        foreach ($items as $item) {
            $order->record(new OrderItemAdded(
                $id,
                $item->id(),
                $item->clientEquipmentId(),
                $item->toolName(),
            ));
        }

        return $order;
    }

    /**
     * @param list<OrderItem> $items
     */
    public static function reconstitute(
        EntityId $id,
        EntityId $clientId,
        Money $estimatedCost,
        DateTimeImmutable $createdAt,
        OrderStatus $status,
        array $items,
        OrderServiceType $serviceType,
        OrderBillingType $billingType,
        OrderUrgency $urgency,
        bool $deliveryRequired = false,
        ?string $defects = null,
        ?string $internalNotes = null,
        ?EntityId $warrantySourceOrderId = null,
    ): self {
        return new self(
            $id,
            $clientId,
            $estimatedCost,
            $createdAt,
            $items,
            $serviceType,
            $billingType,
            $urgency,
            $deliveryRequired,
            $defects,
            $internalNotes,
            $warrantySourceOrderId,
            $status,
        );
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function clientId(): EntityId
    {
        return $this->clientId;
    }

    public function estimatedCost(): Money
    {
        return $this->estimatedCost;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function serviceType(): OrderServiceType
    {
        return $this->serviceType;
    }

    public function billingType(): OrderBillingType
    {
        return $this->billingType;
    }

    public function urgency(): OrderUrgency
    {
        return $this->urgency;
    }

    public function deliveryRequired(): bool
    {
        return $this->deliveryRequired;
    }

    public function defects(): ?string
    {
        return $this->defects;
    }

    public function internalNotes(): ?string
    {
        return $this->internalNotes;
    }

    public function warrantySourceOrderId(): ?EntityId
    {
        return $this->warrantySourceOrderId;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /** @return list<OrderItem> */
    public function items(): array
    {
        return array_values($this->items);
    }

    public function item(EntityId $orderItemId): OrderItem
    {
        $item = $this->items[$orderItemId->value] ?? null;

        if ($item === null) {
            throw new DomainException('Order item not found in this order.');
        }

        return $item;
    }

    public function reassignClient(EntityId $clientId): never
    {
        throw new DomainException('Client cannot be changed after order creation.');
    }

    public function replaceItems(array $items): never
    {
        throw new DomainException('Order items composition cannot be changed after order creation.');
    }

    public function changeEstimatedCost(Money $estimatedCost): never
    {
        throw new DomainException('Estimated cost cannot be changed after order creation.');
    }

    public function completeReception(): void
    {
        $this->assertNotTerminal();

        if ($this->status !== OrderStatus::Created) {
            throw new DomainException('Reception can be completed only from Created status.');
        }

        foreach ($this->items as $item) {
            if (! $item->hasReception()) {
                throw new DomainException('All order items must have reception data before reception is completed.');
            }
        }

        $this->transitionTo(OrderStatus::ReceptionCompleted);
        $this->record(new ReceptionCompleted($this->id));
    }

    public function markInProgress(): void
    {
        $this->assertNotTerminal();
        $this->transitionTo(OrderStatus::InProgress);
    }

    public function markReady(): void
    {
        $this->assertNotTerminal();
        $this->transitionTo(OrderStatus::Ready);
    }

    public function cancel(string $reason): void
    {
        $this->assertNotTerminal();

        if (trim($reason) === '') {
            throw new DomainException('Cancellation reason is required.');
        }

        $this->transitionTo(OrderStatus::Cancelled);
        $this->record(new OrderCancelled($this->id, $reason));
    }

    public function close(): void
    {
        $this->assertNotTerminal();
        $this->transitionTo(OrderStatus::Closed);
        $this->record(new OrderClosed($this->id));
    }

    public function issue(): void
    {
        $this->assertNotTerminal();

        if ($this->status !== OrderStatus::Ready) {
            throw new DomainException('Order can be issued only from Ready status.');
        }

        foreach ($this->items as $item) {
            $item->markIssued();
        }

        $this->transitionTo(OrderStatus::Issued);
        $this->record(new OrderIssued($this->id));
    }

    /**
     * @param list<OrderItem> $items
     */
    private function assertItemsMatchServiceType(array $items, OrderServiceType $serviceType): void
    {
        foreach ($items as $item) {
            if ($serviceType === OrderServiceType::Sharpening) {
                if ($item->toolName() === null || $item->toolType() === null || $item->quantity() === null) {
                    throw new DomainException('Sharpening order items must include tool name, type and quantity.');
                }
            }

            if ($serviceType === OrderServiceType::Repair && $item->clientEquipmentId() === null) {
                throw new DomainException('Repair order items must reference client equipment.');
            }
        }
    }

    private static function assertWarrantySource(
        OrderBillingType $billingType,
        ?EntityId $warrantySourceOrderId,
        EntityId $orderId,
    ): void {
        if ($billingType === OrderBillingType::Warranty) {
            if ($warrantySourceOrderId === null) {
                throw new DomainException('Warranty order requires a source order.');
            }

            if ($warrantySourceOrderId->equals($orderId)) {
                throw new DomainException('Warranty source order cannot be the same order.');
            }

            return;
        }

        if ($warrantySourceOrderId !== null) {
            throw new DomainException('Paid order cannot reference a warranty source order.');
        }
    }

    private function transitionTo(OrderStatus $next): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw new DomainException(sprintf(
                'Order status transition from %s to %s is not allowed.',
                $this->status->value,
                $next->value,
            ));
        }

        $this->status = $next;
    }

    private function assertNotTerminal(): void
    {
        if ($this->status->isTerminal()) {
            throw new DomainException('Terminal order cannot be modified.');
        }
    }

    private static function normalizeText(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
