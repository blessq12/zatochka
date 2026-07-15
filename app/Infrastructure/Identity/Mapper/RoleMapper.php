<?php

namespace App\Infrastructure\Identity\Mapper;

use App\Domain\Identity\Entity\Permission;
use App\Domain\Identity\Entity\Role;
use App\Domain\Identity\VO\PermissionCode;
use App\Infrastructure\Identity\Model\PermissionModel;
use App\Infrastructure\Identity\Model\RoleModel;
use App\Shared\ValueObject\EntityId;

final class RoleMapper
{
    public function toDomain(RoleModel $model): Role
    {
        $permissions = [];

        foreach ($model->permissions as $permission) {
            $permissions[] = new Permission(
                new EntityId((int) $permission->id),
                new PermissionCode((string) $permission->code),
                (string) $permission->description,
            );
        }

        return Role::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->name,
            $permissions,
        );
    }

    public function toPersistence(Role $role, ?RoleModel $model = null): RoleModel
    {
        $model ??= new RoleModel();
        $model->id = $role->id()->value;
        $model->name = $role->name();

        return $model;
    }

    /** @return list<PermissionModel> */
    public function permissionsToPersistence(Role $role): array
    {
        $rows = [];

        foreach ($role->permissions() as $permission) {
            $row = new PermissionModel();
            $row->id = $permission->id->value;
            $row->code = $permission->code->value;
            $row->description = $permission->description;
            $rows[] = $row;
        }

        return $rows;
    }
}
