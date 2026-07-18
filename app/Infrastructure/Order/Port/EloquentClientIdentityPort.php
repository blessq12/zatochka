<?php

namespace App\Infrastructure\Order\Port;

use App\Application\CRM\Command\RegisterClientCommand;
use App\Application\CRM\Command\RegisterClientHandler;
use App\Application\Order\Port\ClientIdentityPort;
use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\CRM\Entity\Client;
use App\Domain\CRM\Repository\ClientRepository;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final readonly class EloquentClientIdentityPort implements ClientIdentityPort
{
    public function __construct(
        private ClientRepository $clients,
        private RegisterClientHandler $registerClient,
        private EntityIdGenerator $ids,
        private DomainEventPublisher $events,
    ) {}

    public function resolveOrRegister(
        ?int $authenticatedClientId,
        string $phone,
        string $fullName,
        ?string $deliveryAddress = null,
    ): int {
        if ($authenticatedClientId !== null && $authenticatedClientId > 0) {
            $client = $this->clients->findById(new EntityId($authenticatedClientId));

            if ($client === null) {
                throw new DomainException('Authenticated client not found.');
            }

            $this->maybeUpdateDeliveryAddress($client, $deliveryAddress);

            return $authenticatedClientId;
        }

        $phoneVo = new Phone($phone);
        $existing = $this->clients->findByPhone($phoneVo);

        if ($existing !== null) {
            $this->maybeUpdateDeliveryAddress($existing, $deliveryAddress);

            return $existing->id()->value;
        }

        $clientId = $this->ids->next('client')->value;
        $this->registerClient->handle(new RegisterClientCommand(
            $clientId,
            $this->ids->next('bonus_account')->value,
            $phone,
            $this->nullableString($fullName),
            null,
        ));

        $created = $this->clients->getById(new EntityId($clientId));
        $this->maybeUpdateDeliveryAddress($created, $deliveryAddress);

        return $clientId;
    }

    private function maybeUpdateDeliveryAddress(Client $client, ?string $deliveryAddress): void
    {
        $normalizedAddress = $this->nullableString($deliveryAddress);

        if ($normalizedAddress === null || $client->deliveryAddress() === $normalizedAddress) {
            return;
        }

        $client->updateProfile(
            null,
            null,
            null,
            null,
            $normalizedAddress,
            false,
            true,
        );
        $this->clients->save($client);
        $this->events->publish($client->pullDomainEvents());
    }

    private function nullableString(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed !== '' ? $trimmed : null;
    }
}
