<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\CRM\Entity\Client;
use App\Domain\CRM\Repository\ClientRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final readonly class RegisterClientHandler
{
    public function __construct(
        private ClientRepository $clients,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RegisterClientCommand $command): void
    {
        $phone = new Phone($command->phone);

        if ($this->clients->findByPhone($phone) !== null) {
            throw new DomainException('Client with this phone already exists.');
        }

        $client = Client::register(
            new EntityId($command->clientId),
            $phone,
            new EntityId($command->bonusAccountId),
            $command->name,
            $command->email !== null ? new Email($command->email) : null,
            $command->birthDate,
            $command->deliveryAddress,
            $command->passwordHash,
        );

        $this->clients->save($client);
        $this->events->publish($client->pullDomainEvents());
    }
}
