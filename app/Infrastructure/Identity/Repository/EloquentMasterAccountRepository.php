<?php

namespace App\Infrastructure\Identity\Repository;

use App\Domain\Identity\Entity\MasterAccount;
use App\Domain\Identity\Repository\MasterAccountRepository;
use App\Infrastructure\Identity\Mapper\MasterAccountMapper;
use App\Infrastructure\Identity\Model\MasterAccountModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentMasterAccountRepository implements MasterAccountRepository
{
    public function __construct(
        private MasterAccountMapper $mapper,
    ) {}

    public function save(MasterAccount $account): void
    {
        $existing = MasterAccountModel::query()->find($account->id()->value);
        $this->mapper->toPersistence($account, $existing)->save();
    }

    public function findById(EntityId $id): ?MasterAccount
    {
        $model = MasterAccountModel::query()->find($id->value);

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function getById(EntityId $id): MasterAccount
    {
        return $this->findById($id)
            ?? throw new DomainException('Master account not found.');
    }

    public function findByEmail(string $email): ?MasterAccount
    {
        $model = MasterAccountModel::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function emailExists(string $email, ?EntityId $exceptId = null): bool
    {
        $query = MasterAccountModel::query()->where('email', strtolower(trim($email)));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId->value);
        }

        return $query->exists();
    }
}
