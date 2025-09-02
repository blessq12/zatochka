<?php

namespace App\Infrastructure\Persistence\Mappers;

use App\Domain\Users\Entities\User as DomainUser;
use App\Domain\Users\ValueObjects\Email;
use App\Domain\Users\ValueObjects\PasswordHash;
use App\Domain\Users\ValueObjects\UserId;
use App\Infrastructure\Persistence\Eloquent\Models\UserModel;

class UserMapper
{
    public static function toDomain(UserModel $model): DomainUser
    {
        return DomainUser::reconstitute(
            UserId::fromString($model->uuid ?? ''),
            $model->name,
            Email::fromString($model->email),
            PasswordHash::fromHash($model->password),
            !$model->is_deleted,
            (bool) $model->is_deleted,
            $model->getRoleNames()->toArray()
        );
    }

    public static function toModel(DomainUser $domain, ?UserModel $model = null): UserModel
    {
        $model = $model ?? new UserModel();
        $model->uuid = (string) $domain->userId();
        $model->name = $domain->name();
        $model->email = (string) $domain->email();
        $model->password = (string) $domain->passwordHash();
        $model->is_deleted = $domain->isDeleted();
        return $model;
    }
}
