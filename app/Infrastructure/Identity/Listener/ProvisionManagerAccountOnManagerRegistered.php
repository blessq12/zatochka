<?php

namespace App\Infrastructure\Identity\Listener;

use App\Application\Identity\Command\ProvisionManagerAccountCommand;
use App\Application\Identity\Command\ProvisionManagerAccountHandler;
use App\Domain\CRM\Event\ManagerAccountProvisioningRequested;

final class ProvisionManagerAccountOnManagerRegistered
{
    public function __construct(
        private ProvisionManagerAccountHandler $provision,
    ) {}

    public function handle(ManagerAccountProvisioningRequested $event): void
    {
        $this->provision->handle(new ProvisionManagerAccountCommand(
            $event->managerId->value,
            $event->email,
            $event->passwordHash,
        ));
    }
}
