<?php

namespace App\Application\Order\Port;

interface ClientProvisioningPort
{
    public function register(
        int $clientId,
        int $bonusAccountId,
        string $phone,
        string $name,
        ?string $email = null,
    ): void;
}
