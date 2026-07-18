<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\Port\PasswordHasher;
use App\Domain\CRM\Repository\ClientRepository;
use App\Shared\ValueObject\EntityId;

final readonly class ChangeClientPortalPasswordHandler
{
    public function __construct(
        private ClientRepository $clients,
        private PasswordHasher $passwords,
        private DomainEventPublisher $events,
    ) {}

    public function handle(ChangeClientPortalPasswordCommand $command): void
    {
        $client = $this->clients->getById(new EntityId($command->clientId));
        $client->setPasswordHash($this->passwords->hash($command->password));
        $this->clients->save($client);
        $this->events->publish($client->pullDomainEvents());
    }
}
