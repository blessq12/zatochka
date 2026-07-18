<?php

namespace App\Application\CRM\DTO;

final readonly class ClientPortalProfileDTO
{
    public function __construct(
        public int $id,
        public ?string $full_name,
        public string $phone,
        public ?string $email,
        public ?string $birth_date,
        public ?string $delivery_address,
        public string $bonus_balance,
        public bool $requires_password_set = false,
    ) {}
}
