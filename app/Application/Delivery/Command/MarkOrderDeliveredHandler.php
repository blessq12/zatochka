<?php

namespace App\Application\Delivery\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Delivery\Repository\DeliveryRequestRepository;
use App\Shared\ValueObject\EntityId;

final readonly class MarkOrderDeliveredHandler
{
    public function __construct(
        private DeliveryRequestRepository $requests,
        private DomainEventPublisher $events,
    ) {}

    public function handle(MarkOrderDeliveredCommand $command): void
    {
        $request = $this->requests->getById(new EntityId($command->deliveryRequestId));
        $request->markDelivered();
        $this->requests->save($request);
        $this->events->publish($request->pullDomainEvents());
    }
}
