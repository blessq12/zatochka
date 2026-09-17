<?php

namespace App\Shared\IntegrationEvents;

final readonly class ActorSoftDeleted
{
    public function __construct(
        public string $actorType,
        public int $actorId,
    ) {}
}
