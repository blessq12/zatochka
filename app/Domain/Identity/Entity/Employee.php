<?php

namespace App\Domain\Identity\Entity;

use App\Domain\Identity\VO\PermissionCode;
use App\Shared\Domain\AggregateRoot;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Email;
use App\Shared\ValueObject\EntityId;

final class Employee extends AggregateRoot
{
    private bool $active = true;

    /** @var array<int, Role> */
    private array $roles = [];

    private function __construct(
        private readonly EntityId $id,
        private string $name,
        private Email $email,
    ) {
        if (trim($this->name) === '') {
            throw new DomainException('Employee name cannot be empty.');
        }
    }

    public static function hire(EntityId $id, string $name, Email $email): self
    {
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

    public function email(): Email
    {
        return $this->email;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    /** @return list<Role> */
    public function roles(): array
    {
        return array_values($this->roles);
    }

    public function rename(string $name): void
    {
        if (trim($name) === '') {
            throw new DomainException('Employee name cannot be empty.');
        }

        $this->name = $name;
    }

    public function changeEmail(Email $email): void
    {
        $this->email = $email;
    }

    public function assignRole(Role $role): void
    {
        $this->roles[$role->id()->value] = $role;
    }

    public function removeRole(EntityId $roleId): void
    {
        unset($this->roles[$roleId->value]);
    }

    public function deactivate(): void
    {
        $this->active = false;
    }

    public function activate(): void
    {
        $this->active = true;
    }

    public function can(PermissionCode $code): bool
    {
        if (! $this->active) {
            return false;
        }

        foreach ($this->roles as $role) {
            if ($role->allows($code)) {
                return true;
            }
        }

        return false;
    }
}
