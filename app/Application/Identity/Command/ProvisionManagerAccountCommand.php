<?php

namespace App\Application\Identity\Command;

final readonly class ProvisionManagerAccountCommand
{
    public function __construct(
        public int $managerId,
        public string $email,
        public string $passwordHash,
    ) {}
}
