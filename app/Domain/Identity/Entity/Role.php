<?php

namespace App\Domain\Identity\Entity;

use App\Domain\Identity\VO\PermissionCode;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class Role
{
    /** @var array<string, Permission> */
    private array $permissions = [];

    public function __construct(
        private readonly EntityId $id,
        private readonly string $name,
    ) {
        if (trim($this->name) === '') {
            throw new DomainException('Role name cannot be empty.');
        }
    }

    /**
     * @param list<Permission> $permissions
     */
    public static function reconstitute(
        EntityId $id,
        string $name,
        array $permissions = [],
    ): self {
        $role = new self($id, $name);

        foreach ($permissions as $permission) {
            $role->permissions[$permission->code->value] = $permission;
        }

        return $role;
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    /** @return list<Permission> */
    public function permissions(): array
    {
        return array_values($this->permissions);
    }

    public function grant(Permission $permission): void
    {
        $this->permissions[$permission->code->value] = $permission;
    }

    public function revoke(PermissionCode $code): void
    {
        unset($this->permissions[$code->value]);
    }

    public function allows(PermissionCode $code): bool
    {
        return isset($this->permissions[$code->value]);
    }
}
