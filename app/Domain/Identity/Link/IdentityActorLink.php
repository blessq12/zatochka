<?php

namespace App\Domain\Identity\Link;

final readonly class IdentityActorLink
{
    public function __construct(
        public int $identityId,
        public string $actorType,
        public int $actorId,
    ) {}
}
