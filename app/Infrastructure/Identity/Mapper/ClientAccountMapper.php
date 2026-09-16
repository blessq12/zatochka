<?php

namespace App\Infrastructure\Identity\Mapper;

use App\Domain\Identity\Entity\ClientAccount;
use App\Infrastructure\Identity\Model\ClientAccountModel;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final class ClientAccountMapper
{
    public function toDomain(ClientAccountModel $model): ClientAccount
    {
        return ClientAccount::reconstitute(
            new EntityId((int) $model->id),
            new EntityId((int) $model->client_id),
            new Phone((string) $model->phone),
            (string) $model->password,
        );
    }

    public function toPersistence(ClientAccount $account, ?ClientAccountModel $model = null): ClientAccountModel
    {
        $model ??= new ClientAccountModel;
        $model->id = $account->id()->value;
        $model->client_id = $account->clientId()->value;
        $model->phone = $account->phone()->value;
        $model->password = $account->passwordHash();

        return $model;
    }
}
