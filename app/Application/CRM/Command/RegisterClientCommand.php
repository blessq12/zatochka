<?php

namespace App\Application\CRM\Command;

final readonly class RegisterClientCommand
{
    public function __construct(
        public int $clientId,
        public int $bonusAccountId,
        public string $phone,
        public ?string $name = null,
        public ?string $email = null,
    ) {}
}
