<?php

namespace App\Domain\Client\Event;

use Spatie\EventSourcing\StoredEvents\ShouldBeStored;

class ClientRegistered extends ShouldBeStored
{
    public function __construct(
        public readonly string $fullName,
        public readonly string $phone,
        public readonly ?string $email = null,
        public readonly ?string $telegram = null,
        public readonly ?string $birthDate = null,
        public readonly ?string $deliveryAddress = null,
        public readonly ?string $password = null
    ) {}
}
