<?php

namespace App\Application\Delivery\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Delivery\Repository\DeliveryRequestRepository;
use App\Domain\Delivery\Service\CourierDispatchService;
use App\Shared\ValueObject\EntityId;

final readonly class AssignCourierHandler
{
    public function __construct(
        private DeliveryRequestRepository $requests,
        private CourierDispatchService $dispatch,
        private DomainEventPublisher $events,
    ) {}

    public function handle(AssignCourierCommand $command): void
    {
        $request = $this->requests->getById(new EntityId($command->deliveryRequestId));
        $this->dispatch->assign($request, new EntityId($command->courierId));
        $this->requests->save($request);
        $this->events->publish($request->pullDomainEvents());
    }
}
