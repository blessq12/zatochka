<?php

namespace App\Application\Identity\Command;

final readonly class ProvisionClientAccountCommand
{
    public function __construct(
        public int $accountId,
        public int $clientId,
        public string $phone,
        public string $passwordHash,
    ) {}
}
