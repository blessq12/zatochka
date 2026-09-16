<?php

namespace App\Domain\Identity\Entity;

use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

/** Auth principal for a CRM Master. Id equals master person id. */
final class MasterAccount extends AggregateRoot
{
    private function __construct(
        private readonly EntityId $id,
        private string $email,
        private string $passwordHash,
    ) {}

    public static function provision(
        EntityId $id,
        string $email,
        string $passwordHash,
    ): self {
        $email = strtolower(trim($email));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Master account email is invalid.');
        }

        if ($passwordHash === '') {
            throw new DomainException('Master account password is required.');
        }

        return new self($id, $email, $passwordHash);
    }

    public static function reconstitute(
        EntityId $id,
        string $email,
        string $passwordHash,
    ): self {
        return new self($id, $email, $passwordHash);
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function changePassword(string $passwordHash): void
    {
        if ($passwordHash === '') {
            throw new DomainException('Master account password is required.');
        }

        $this->passwordHash = $passwordHash;
    }

    public function syncEmail(string $email): void
    {
        $email = strtolower(trim($email));

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Master account email is invalid.');
        }

        $this->email = $email;
    }
}
