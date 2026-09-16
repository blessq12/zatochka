<?php

namespace App\Application\Identity\Command;

final readonly class ProvisionMasterAccountCommand
{
    public function __construct(
        public int $masterId,
        public string $email,
        public string $passwordHash,
    ) {}
}
