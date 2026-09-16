<?php

namespace App\Infrastructure\Identity\Repository;

use App\Domain\Identity\Entity\ManagerAccount;
use App\Domain\Identity\Repository\ManagerAccountRepository;
use App\Infrastructure\Identity\Mapper\ManagerAccountMapper;
use App\Infrastructure\Identity\Model\ManagerAccountModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentManagerAccountRepository implements ManagerAccountRepository
{
    public function __construct(
        private ManagerAccountMapper $mapper,
    ) {}

    public function save(ManagerAccount $account): void
    {
        $existing = ManagerAccountModel::query()->find($account->id()->value);
        $this->mapper->toPersistence($account, $existing)->save();
    }

    public function findById(EntityId $id): ?ManagerAccount
    {
        $model = ManagerAccountModel::query()->find($id->value);

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function getById(EntityId $id): ManagerAccount
    {
        return $this->findById($id)
            ?? throw new DomainException('Manager account not found.');
    }

    public function findByEmail(string $email): ?ManagerAccount
    {
        $model = ManagerAccountModel::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function emailExists(string $email, ?EntityId $exceptId = null): bool
    {
        $query = ManagerAccountModel::query()->where('email', strtolower(trim($email)));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId->value);
        }

        return $query->exists();
    }
}
