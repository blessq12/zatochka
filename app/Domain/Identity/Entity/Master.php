<?php

namespace App\Domain\Identity\Entity;

use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class Master extends AggregateRoot
{
    private function __construct(
        private readonly EntityId $id,
        private string $name,
        private string $email,
        private string $passwordHash,
    ) {}

    public static function register(
        EntityId $id,
        string $name,
        string $email,
        string $passwordHash,
    ): self {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '') {
            throw new DomainException('Master name is required.');
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Master email is invalid.');
        }

        if ($passwordHash === '') {
            throw new DomainException('Master password is required.');
        }

        return new self($id, $name, $email, $passwordHash);
    }

    public static function reconstitute(
        EntityId $id,
        string $name,
        string $email,
        string $passwordHash,
    ): self {
        return new self($id, $name, $email, $passwordHash);
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function email(): string
    {
        return $this->email;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function updateProfile(string $name, string $email): void
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '') {
            throw new DomainException('Master name is required.');
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Master email is invalid.');
        }

        $this->name = $name;
        $this->email = $email;
    }

    public function changePassword(string $passwordHash): void
    {
        if ($passwordHash === '') {
            throw new DomainException('Master password is required.');
        }

        $this->passwordHash = $passwordHash;
    }
}
