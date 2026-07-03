<?php

namespace App\Application\ClientPortal\Command;

final readonly class RegisterClientCommand
{
    public function __construct(
        public string $phone,
        public string $fullName,
        public string $password,
        public ?string $email = null,
        public ?string $birthDate = null,
        public ?string $deliveryAddress = null,
    ) {}
}
