<?php

namespace App\Application\ClientPortal\Command;

final readonly class CreateClientCommand
{
    public function __construct(
        public string $phone,
        public string $fullName,
        public ?string $email = null,
        public ?string $birthDate = null,
        public ?string $deliveryAddress = null,
    ) {}
}
