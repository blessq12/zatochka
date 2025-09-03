<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Facades\Log;

use App\Domain\Shared\Interfaces\UserRepositoryInterface;
use App\Domain\Users\Entities\User as DomainUser;
use App\Domain\Users\ValueObjects\Email;
use App\Models\User as UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function save(DomainUser $user): void
    {
        $model = UserModel::query()->where('id', $user->userId())->first();
        $model = UserMapper::toModel($user, $model);

        // Sync roles before save to ensure they are properly assigned
        $model->syncRoles($user->roles());

        $model->save();
    }

    public function getById(int $id): ?DomainUser
    {
        $model = UserModel::query()->where('id', $id)->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function getByEmail(Email $email): ?DomainUser
    {
        $model = UserModel::query()->where('email', (string) $email)->first();
        return $model ? UserMapper::toDomain($model) : null;
    }

    public function existsByEmail(Email $email): bool
    {
        return UserModel::query()->where('email', (string) $email)->exists();
    }
}
