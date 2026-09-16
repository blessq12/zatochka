<?php

namespace App\Infrastructure\Identity\Repository;

use App\Domain\Identity\Entity\ClientAccount;
use App\Domain\Identity\Repository\ClientAccountRepository;
use App\Infrastructure\Identity\Mapper\ClientAccountMapper;
use App\Infrastructure\Identity\Model\ClientAccountModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Phone;

final readonly class EloquentClientAccountRepository implements ClientAccountRepository
{
    public function __construct(
        private ClientAccountMapper $mapper,
    ) {}

    public function save(ClientAccount $account): void
    {
        $existing = ClientAccountModel::query()->find($account->id()->value);
        $this->mapper->toPersistence($account, $existing)->save();
    }

    public function findById(EntityId $id): ?ClientAccount
    {
        $model = ClientAccountModel::query()->find($id->value);

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function findByClientId(EntityId $clientId): ?ClientAccount
    {
        $model = ClientAccountModel::query()->where('client_id', $clientId->value)->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function findByPhone(Phone $phone): ?ClientAccount
    {
        $model = ClientAccountModel::query()->where('phone', $phone->value)->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function getByClientId(EntityId $clientId): ClientAccount
    {
        return $this->findByClientId($clientId)
            ?? throw new DomainException('Client account not found.');
    }
}
