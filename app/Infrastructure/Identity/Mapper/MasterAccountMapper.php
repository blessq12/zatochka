<?php

namespace App\Infrastructure\Identity\Mapper;

use App\Domain\Identity\Entity\MasterAccount;
use App\Infrastructure\Identity\Model\MasterAccountModel;
use App\Shared\ValueObject\EntityId;

final class MasterAccountMapper
{
    public function toDomain(MasterAccountModel $model): MasterAccount
    {
        return MasterAccount::reconstitute(
            new EntityId((int) $model->id),
            (string) $model->email,
            (string) $model->password,
        );
    }

    public function toPersistence(MasterAccount $account, ?MasterAccountModel $model = null): MasterAccountModel
    {
        $model ??= new MasterAccountModel;
        $model->id = $account->id()->value;
        $model->email = $account->email();
        $model->password = $account->passwordHash();

        return $model;
    }
}
