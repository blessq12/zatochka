<?php

namespace App\Domain\Client\AggregateRoot;

use App\Domain\Client\Event\ClientCreated;
use App\Domain\Client\Event\ClientRegistered;
use App\Domain\Client\Event\ClientLoggedIn;
use Spatie\EventSourcing\AggregateRoots\AggregateRoot;
use Illuminate\Support\Str;

class ClientAggregateRoot extends AggregateRoot
{
    public function createClient(int $clientId): self
    {
        $this->recordThat(new ClientCreated(
            clientId: $clientId
        ));

        return $this;
    }

    public function registerClient(array $clientData): self
    {
        $this->recordThat(new ClientRegistered(
            fullName: $clientData['full_name'],
            phone: $clientData['phone'],
            email: $clientData['email'] ?? null,
            telegram: $clientData['telegram'] ?? null,
            birthDate: $clientData['birth_date'] ?? null,
            deliveryAddress: $clientData['delivery_address'] ?? null,
            password: $clientData['password'] ?? null
        ));

        return $this;
    }

    public function loginClient(string $phone): self
    {
        $this->recordThat(new ClientLoggedIn(
            phone: $phone
        ));

        return $this;
    }

    public static function create(): self
    {
        return static::retrieve(Str::uuid()->toString());
    }
}
