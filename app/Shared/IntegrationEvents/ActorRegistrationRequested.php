<?php

namespace App\Shared\IntegrationEvents;

final readonly class ActorRegistrationRequested
{
    public function __construct(
        public int $identityId,
        public string $actorType,
        public string $email,
        public ?string $name = null,
        public ?string $phone = null,
        public ?string $birthday = null,
        public ?string $deliveryAddress = null,
    ) {}
}
