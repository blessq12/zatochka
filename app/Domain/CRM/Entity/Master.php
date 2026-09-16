<?php

namespace App\Domain\CRM\Entity;

use App\Domain\CRM\Event\MasterAccountProvisioningRequested;
use App\Domain\CRM\Event\MasterUpdated;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class Master extends AggregateRoot
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
            throw new DomainException('Master name is required.');
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Master email is invalid.');
        }

        if ($passwordHash === '') {
            throw new DomainException('Master password is required.');
        }

        $master = new self($id, $name, $email);
        $master->record(new MasterAccountProvisioningRequested($id, $email, $passwordHash));

        return $master;
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
            throw new DomainException('Master name is required.');
        }

        if ($email === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            throw new DomainException('Master email is invalid.');
        }

        $this->name = $name;
        $this->email = $email;
        $this->record(new MasterUpdated($this->id, $this->email));
    }
}
