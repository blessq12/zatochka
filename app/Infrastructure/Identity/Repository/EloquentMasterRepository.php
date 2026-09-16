<?php

namespace App\Infrastructure\Identity\Repository;

use App\Domain\Identity\Entity\Master;
use App\Domain\Identity\Repository\MasterRepository;
use App\Infrastructure\Identity\Mapper\MasterMapper;
use App\Infrastructure\Identity\Model\MasterModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentMasterRepository implements MasterRepository
{
    public function __construct(
        private MasterMapper $mapper,
    ) {}

    public function save(Master $master): void
    {
        $existing = MasterModel::query()->find($master->id()->value);
        $this->mapper->toPersistence($master, $existing)->save();
    }

    public function findById(EntityId $id): ?Master
    {
        $model = MasterModel::query()->find($id->value);

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function getById(EntityId $id): Master
    {
        return $this->findById($id)
            ?? throw new DomainException('Master not found.');
    }

    public function findByEmail(string $email): ?Master
    {
        $model = MasterModel::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function emailExists(string $email, ?EntityId $exceptId = null): bool
    {
        $query = MasterModel::query()->where('email', strtolower(trim($email)));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId->value);
        }

        return $query->exists();
    }

    public function delete(EntityId $id): void
    {
        MasterModel::query()->whereKey($id->value)->delete();
    }
}
