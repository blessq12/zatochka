<?php

namespace App\Application\Crm\Listener;

use App\Application\Crm\Command\CreateActorHandler;
use App\Domain\Crm\ActorType;
use App\Shared\EventBus\EventBus;
use App\Shared\IntegrationEvents\ActorCreated;
use App\Shared\IntegrationEvents\ActorRegistrationRequested;

final readonly class CreateActorOnRegistrationRequested
{
    public function __construct(
        private CreateActorHandler $createActor,
        private EventBus $events,
    ) {}

    public function handle(ActorRegistrationRequested $event): void
    {
        $actorType = ActorType::fromRoute($event->actorType);
        $actor = $this->createActor->handle(
            $actorType,
            $event->name,
            $event->phone,
            $event->birthday,
        );

        $this->events->publish(new ActorCreated(
            $event->identityId,
            $event->actorType,
            $actor->id,
            $actor->profileAdditionalId,
        ));
    }
}
