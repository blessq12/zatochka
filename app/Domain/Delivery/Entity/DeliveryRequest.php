<?php

namespace App\Domain\Delivery\Entity;

use App\Domain\Delivery\Event\CourierAssigned;
use App\Domain\Delivery\Event\DeliveryRequested;
use App\Domain\Delivery\Event\EquipmentCollected;
use App\Domain\Delivery\Event\OrderDelivered;
use App\Domain\Delivery\VO\DeliveryAddress;
use App\Domain\Delivery\VO\DeliveryStatus;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class DeliveryRequest extends AggregateRoot
{
    private DeliveryStatus $status;
    private ?CourierAssignment $courierAssignment = null;

    private function __construct(
        private readonly EntityId $id,
        private readonly EntityId $orderId,
        private readonly DeliveryAddress $address,
        private readonly bool $pickup,
    ) {
        $this->status = DeliveryStatus::Requested;
    }

    public static function request(
        EntityId $id,
        EntityId $orderId,
        DeliveryAddress $address,
        bool $pickup = false,
    ): self {
        $request = new self($id, $orderId, $address, $pickup);
        $request->record(new DeliveryRequested($id, $orderId));

        return $request;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function orderId(): EntityId
    {
        return $this->orderId;
    }

    public function address(): DeliveryAddress
    {
        return $this->address;
    }

    public function isPickup(): bool
    {
        return $this->pickup;
    }

    public function status(): DeliveryStatus
    {
        return $this->status;
    }

    public function courierAssignment(): ?CourierAssignment
    {
        return $this->courierAssignment;
    }

    public function assignCourier(EntityId $courierId): void
    {
        $this->assertNotTerminal();

        if ($this->courierAssignment !== null) {
            throw new DomainException('Courier is already assigned.');
        }

        $this->courierAssignment = new CourierAssignment($courierId);
        $this->transitionTo(DeliveryStatus::CourierAssigned);
        $this->record(new CourierAssigned($this->id, $courierId));
    }

    public function markCollected(): void
    {
        $this->assertNotTerminal();
        $this->transitionTo(DeliveryStatus::Collected);
        $this->record(new EquipmentCollected($this->id, $this->orderId));
    }

    public function markDelivered(): void
    {
        $this->assertNotTerminal();
        $this->transitionTo(DeliveryStatus::Delivered);
        $this->record(new OrderDelivered($this->id, $this->orderId));
    }

    private function transitionTo(DeliveryStatus $next): void
    {
        if (! $this->status->canTransitionTo($next)) {
            throw new DomainException(sprintf(
                'Delivery status transition from %s to %s is not allowed.',
                $this->status->value,
                $next->value,
            ));
        }

        $this->status = $next;
    }

    private function assertNotTerminal(): void
    {
        if ($this->status->isTerminal()) {
            throw new DomainException('Terminal delivery request cannot be modified.');
        }
    }
}
