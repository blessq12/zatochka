<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\CRM\Repository\ClientRepository;
use App\Shared\ValueObject\EntityId;

final readonly class AccrueBonusHandler
{
    public function __construct(
        private ClientRepository $clients,
        private DomainEventPublisher $events,
    ) {}

    public function handle(AccrueBonusCommand $command): void
    {
        $client = $this->clients->getById(new EntityId($command->clientId));
        $client->accrueBonus($command->amount);
        $this->clients->save($client);
        $this->events->publish($client->pullDomainEvents());
    }
}
