<?php

namespace App\Application\CRM\Command;

final readonly class UpdateClientCommand
{
    public function __construct(
        public int $clientId,
        public ?string $name = null,
        public ?string $phone = null,
        public ?string $email = null,
    ) {}
}
