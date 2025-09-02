<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use Illuminate\Support\Facades\Log;

use App\Domain\Shared\Interfaces\UserRepositoryInterface;
use App\Domain\Users\Entities\User as DomainUser;
use App\Domain\Users\ValueObjects\Email;
use App\Domain\Users\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;
use App\Infrastructure\Persistence\Mappers\UserMapper;
use Ramsey\Uuid\Uuid as RamseyUuid;

class EloquentUserRepository implements UserRepositoryInterface
{
    public function nextId(): UserId
    {
        return UserId::fromString(RamseyUuid::uuid4()->toString());
    }

        public function save(DomainUser $user): void
    {
        $model = UserModel::query()->where('uuid', (string) $user->userId())->first();
        $model = UserMapper::toModel($user, $model);

        // Sync roles before save to ensure they are properly assigned
        $model->syncRoles($user->roles());

        $model->save();
    }

    public function getById(UserId $id): ?DomainUser
    {
        $model = UserModel::query()->where('uuid', (string) $id)->first();
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
