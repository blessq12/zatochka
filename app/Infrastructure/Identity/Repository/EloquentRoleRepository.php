<?php

namespace App\Infrastructure\Identity\Repository;

use App\Domain\Identity\Entity\Role;
use App\Domain\Identity\Repository\RoleRepository;
use App\Infrastructure\Identity\Mapper\RoleMapper;
use App\Infrastructure\Identity\Model\PermissionModel;
use App\Infrastructure\Identity\Model\RoleModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Facades\DB;

final readonly class EloquentRoleRepository implements RoleRepository
{
    public function __construct(
        private RoleMapper $mapper,
    ) {}

    public function save(Role $role): void
    {
        DB::transaction(function () use ($role): void {
            $model = RoleModel::query()->find($role->id()->value);
            $model = $this->mapper->toPersistence($role, $model);
            $model->save();

            $permissionIds = [];

            foreach ($this->mapper->permissionsToPersistence($role) as $permission) {
                PermissionModel::query()->updateOrCreate(
                    ['id' => $permission->id],
                    [
                        'code' => $permission->code,
                        'description' => $permission->description,
                    ],
                );
                $permissionIds[] = $permission->id;
            }

            $model->permissions()->sync($permissionIds);
        });
    }

    public function findById(EntityId $id): ?Role
    {
        $model = RoleModel::query()->with('permissions')->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): Role
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Role %d not found.', $id->value));
    }
}
