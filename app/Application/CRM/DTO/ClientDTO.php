<?php

namespace App\Application\CRM\DTO;

final readonly class ClientDTO
{
    public function __construct(
        public int $id,
        public string $phone,
        public ?string $name,
        public ?string $email,
        public string $bonusBalance,
        public ?string $birthDate = null,
        public ?string $deliveryAddress = null,
    ) {}
}
