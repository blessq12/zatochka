<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\CRM\Repository\ClientRepository;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final readonly class UpdateClientHandler
{
    public function __construct(
        private ClientRepository $clients,
        private DomainEventPublisher $events,
    ) {}

    public function handle(UpdateClientCommand $command): void
    {
        $client = $this->clients->getById(new EntityId($command->clientId));
        $client->updateProfile(
            $command->name,
            $command->phone !== null ? new Phone($command->phone) : null,
            $command->email !== null ? new Email($command->email) : null,
        );
        $this->clients->save($client);
        $this->events->publish($client->pullDomainEvents());
    }
}
