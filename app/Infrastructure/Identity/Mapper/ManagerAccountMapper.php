<?php

namespace App\Infrastructure\Identity\Mapper;

use App\Domain\Identity\Entity\ManagerAccount;
use App\Infrastructure\Identity\Model\ManagerAccountModel;
use App\Shared\ValueObject\EntityId;

final class ManagerAccountMapper
{
    public function toDomain(ManagerAccountModel $model): ManagerAccount
    {
        return ManagerAccount::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->email,
            (string) $model->password,
        );
    }

    public function toPersistence(ManagerAccount $account, ?ManagerAccountModel $model = null): ManagerAccountModel
    {
        $model ??= new ManagerAccountModel;
        $model->id = $account->id()->value;
        $model->email = $account->email();
        $model->password = $account->passwordHash();

        return $model;
    }
}
