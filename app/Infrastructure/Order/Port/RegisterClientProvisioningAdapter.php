<?php

namespace App\Infrastructure\Order\Port;

use App\Application\CRM\Command\RegisterClientCommand;
use App\Application\CRM\Command\RegisterClientHandler;
use App\Application\Order\Port\ClientProvisioningPort;

final readonly class RegisterClientProvisioningAdapter implements ClientProvisioningPort
{
    public function __construct(
        private RegisterClientHandler $registerClient,
    ) {}

    public function register(
        int $clientId,
        int $bonusAccountId,
        string $phone,
        string $name,
        ?string $email = null,
    ): void {
        $this->registerClient->handle(new RegisterClientCommand(
            $clientId,
            $bonusAccountId,
            $phone,
            $name,
            $email,
        ));
    }
}
