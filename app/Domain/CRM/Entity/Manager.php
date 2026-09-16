<?php

namespace App\Domain\CRM\Entity;

use App\Domain\CRM\Event\ManagerAccountProvisioningRequested;
use App\Domain\CRM\Event\ManagerUpdated;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class Manager extends AggregateRoot
{
    private function __construct(
        private readonly EntityId $id,
        private string $name,
        private string $email,
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
            throw new DomainException('Manager name is required.');
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Manager email is invalid.');
        }

        if ($passwordHash === '') {
            throw new DomainException('Manager password is required.');
        }

        $manager = new self($id, $name, $email);
        $manager->record(new ManagerAccountProvisioningRequested($id, $email, $passwordHash));

        return $manager;
    }

    public static function reconstitute(
        EntityId $id,
        string $name,
        string $email,
    ): self {
        return new self($id, $name, $email);
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

    public function updateProfile(string $name, string $email): void
    {
        $name = trim($name);
        $email = strtolower(trim($email));

        if ($name === '') {
            throw new DomainException('Manager name is required.');
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Manager email is invalid.');
        }

        $this->name = $name;
        $this->email = $email;
        $this->record(new ManagerUpdated($this->id, $this->email));
    }
}
