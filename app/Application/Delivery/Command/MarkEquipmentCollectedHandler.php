<?php

namespace App\Application\Delivery\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Delivery\Repository\DeliveryRequestRepository;
use App\Shared\ValueObject\EntityId;

final readonly class MarkEquipmentCollectedHandler
{
    public function __construct(
        private DeliveryRequestRepository $requests,
        private DomainEventPublisher $events,
    ) {}

    public function handle(MarkEquipmentCollectedCommand $command): void
    {
        $request = $this->requests->getById(new EntityId($command->deliveryRequestId));
        $request->markCollected();
        $this->requests->save($request);
        $this->events->publish($request->pullDomainEvents());
    }
}
