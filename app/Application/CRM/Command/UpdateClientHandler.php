<?php

namespace App\Application\CRM\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\CRM\Repository\ClientRepository;
use App\Shared\Domain\DomainException;
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

        if ($command->phone !== null) {
            $phone = new Phone($command->phone);
            $existing = $this->clients->findByPhone($phone);
            if ($existing !== null && $existing->id()->value !== $command->clientId) {
                throw new DomainException('Client with this phone already exists.');
            }
        }

        $client->updateProfile(
            $command->name,
            $command->phone !== null ? new Phone($command->phone) : null,
            $command->email !== null ? new Email($command->email) : null,
            $command->birthDate,
            $command->deliveryAddress,
            $command->updateBirthDate,
            $command->updateDeliveryAddress,
        );
        $this->clients->save($client);
        $this->events->publish($client->pullDomainEvents());
    }
}
