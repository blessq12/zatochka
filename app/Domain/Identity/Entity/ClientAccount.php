<?php

namespace App\Domain\Identity\Entity;

use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final class ClientAccount extends AggregateRoot
{
    private function __construct(
        private readonly EntityId $id,
        private readonly EntityId $clientId,
        private Phone $phone,
        private string $passwordHash,
    ) {}

    public static function provision(
        EntityId $id,
        EntityId $clientId,
        Phone $phone,
        string $passwordHash,
    ): self {
        if ($passwordHash === '') {
            throw new DomainException('Client account password is required.');
        }

        return new self($id, $clientId, $phone, $passwordHash);
    }

    public static function reconstitute(
        EntityId $id,
        EntityId $clientId,
        Phone $phone,
        string $passwordHash,
    ): self {
        return new self($id, $clientId, $phone, $passwordHash);
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function clientId(): EntityId
    {
        return $this->clientId;
    }

    public function phone(): Phone
    {
        return $this->phone;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function changePassword(string $passwordHash): void
    {
        if ($passwordHash === '') {
            throw new DomainException('Client account password is required.');
        }

        $this->passwordHash = $passwordHash;
    }

    public function syncPhone(Phone $phone): void
    {
        $this->phone = $phone;
    }
}
