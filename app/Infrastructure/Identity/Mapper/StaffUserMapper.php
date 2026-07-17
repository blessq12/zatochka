<?php

namespace App\Infrastructure\Identity\Mapper;

use App\Domain\Identity\Entity\StaffUser;
use App\Domain\Identity\VO\StaffRole;
use App\Models\User;
use App\Shared\ValueObject\EntityId;

final class StaffUserMapper
{
    public function toDomain(User $model): StaffUser
    {
        return StaffUser::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->name,
            (string) $model->email,
            StaffRole::from((string) $model->role->value),
            (string) $model->password,
        );
    }

    public function toPersistence(StaffUser $user, ?User $model = null): User
    {
        $model ??= new User;
        $model->id = $user->id()->value;
        $model->name = $user->name();
        $model->email = $user->email();
        $model->role = $user->role()->value;
        $model->password = $user->passwordHash();

        return $model;
    }
}
