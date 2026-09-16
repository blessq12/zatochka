<?php

namespace App\Infrastructure\Identity\Listener;

use App\Application\Identity\Command\ProvisionClientAccountCommand;
use App\Application\Identity\Command\ProvisionClientAccountHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\CRM\Event\ClientPortalSignUpRequested;

final class ProvisionClientAccountOnPortalSignUp
{
    public function __construct(
        private ProvisionClientAccountHandler $provision,
        private EntityIdGenerator $ids,
    ) {}

    public function handle(ClientPortalSignUpRequested $event): void
    {
        $this->provision->handle(new ProvisionClientAccountCommand(
            $this->ids->next('client_account')->value,
            $event->clientId->value,
            $event->phone,
            $event->passwordHash,
        ));
    }
}
