<?php

namespace App\Infrastructure\Identity\Listener;

use App\Application\Identity\Command\ProvisionMasterAccountCommand;
use App\Application\Identity\Command\ProvisionMasterAccountHandler;
use App\Domain\CRM\Event\MasterAccountProvisioningRequested;

final class ProvisionMasterAccountOnMasterRegistered
{
    public function __construct(
        private ProvisionMasterAccountHandler $provision,
    ) {}

    public function handle(MasterAccountProvisioningRequested $event): void
    {
        $this->provision->handle(new ProvisionMasterAccountCommand(
            $event->masterId->value,
            $event->email,
            $event->passwordHash,
        ));
    }
}
