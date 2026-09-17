<?php

namespace App\Shared\IntegrationEvents;

final readonly class ActorCreated
{
    public function __construct(
        public int $identityId,
        public string $actorType,
        public int $actorId,
        public int $profileAdditionalId,
    ) {}
}
