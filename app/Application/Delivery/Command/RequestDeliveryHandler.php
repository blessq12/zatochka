<?php

namespace App\Application\Delivery\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Delivery\Entity\DeliveryRequest;
use App\Domain\Delivery\Repository\DeliveryRequestRepository;
use App\Domain\Delivery\VO\DeliveryAddress;
use App\Shared\ValueObject\EntityId;

final readonly class RequestDeliveryHandler
{
    public function __construct(
        private DeliveryRequestRepository $requests,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RequestDeliveryCommand $command): void
    {
        $request = DeliveryRequest::request(
            new EntityId($command->deliveryRequestId),
            new EntityId($command->orderId),
            new DeliveryAddress(
                $command->city,
                $command->street,
                $command->building,
                $command->apartment,
                $command->comment,
            ),
            $command->pickup,
        );

        $this->requests->save($request);
        $this->events->publish($request->pullDomainEvents());
    }
}
