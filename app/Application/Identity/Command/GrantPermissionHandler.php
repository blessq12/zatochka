<?php

namespace App\Application\Identity\Command;

use App\Domain\Identity\Entity\Permission;
use App\Domain\Identity\Repository\RoleRepository;
use App\Domain\Identity\VO\PermissionCode;
use App\Shared\ValueObject\EntityId;

final readonly class GrantPermissionHandler
{
    public function __construct(
        private RoleRepository $roles,
    ) {}

    public function handle(GrantPermissionCommand $command): void
    {
        $role = $this->roles->getById(new EntityId($command->roleId));
        $role->grant(new Permission(
            new EntityId($command->permissionId),
            new PermissionCode($command->permissionCode),
            $command->description,
        ));
        $this->roles->save($role);
    }
}
