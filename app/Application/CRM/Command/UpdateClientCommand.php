<?php

namespace App\Application\CRM\Command;

final readonly class UpdateClientCommand
{
    public function __construct(
        public int $clientId,
        public ?string $name = null,
        public ?string $phone = null,
        public ?string $email = null,
        public ?string $birthDate = null,
        public ?string $deliveryAddress = null,
        public bool $updateBirthDate = false,
        public bool $updateDeliveryAddress = false,
    ) {}
}
