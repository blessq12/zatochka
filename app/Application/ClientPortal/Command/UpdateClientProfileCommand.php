<?php

namespace App\Application\ClientPortal\Command;

final readonly class UpdateClientProfileCommand
{
    public function __construct(
        public int $clientId,
        public string $fullName,
        public ?string $email = null,
        public ?string $birthDate = null,
        public ?string $deliveryAddress = null,
    ) {}
}
