<?php

namespace App\Domain\Identity\Entity;

use App\Domain\Identity\VO\StaffRole;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class StaffUser extends AggregateRoot
{
    private function __construct(
        private readonly EntityId $id,
        private string $name,
        private string $email,
        private StaffRole $role,
        private string $passwordHash,
    ) {}

    public static function register(
        EntityId $id,
        string $name,
        string $email,
        StaffRole $role,
        string $passwordHash,
    ): self {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '') {
            throw new DomainException('Staff name is required.');
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Staff email is invalid.');
        }

        if ($passwordHash === '') {
            throw new DomainException('Staff password is required.');
        }

        return new self($id, $name, $email, $role, $passwordHash);
    }

    public static function reconstitute(
        EntityId $id,
        string $name,
        string $email,
        StaffRole $role,
        string $passwordHash,
    ): self {
        return new self($id, $name, $email, $role, $passwordHash);
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

    public function role(): StaffRole
    {
        return $this->role;
    }

    public function passwordHash(): string
    {
        return $this->passwordHash;
    }

    public function isMaster(): bool
    {
        return $this->role === StaffRole::Master;
    }

    public function updateProfile(string $name, string $email, StaffRole $role): void
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '') {
            throw new DomainException('Staff name is required.');
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Staff email is invalid.');
        }

        $this->name = $name;
        $this->email = $email;
        $this->role = $role;
    }

    public function changePassword(string $passwordHash): void
    {
        if ($passwordHash === '') {
            throw new DomainException('Staff password is required.');
        }

        $this->passwordHash = $passwordHash;
    }
}
