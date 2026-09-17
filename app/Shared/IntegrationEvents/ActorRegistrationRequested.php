<?php

namespace App\Shared\IntegrationEvents;

final readonly class ActorRegistrationRequested
{
    public function __construct(
        public int $identityId,
        public string $actorType,
    ) {}
}
