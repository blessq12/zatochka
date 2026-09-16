<?php

namespace App\Infrastructure\CRM\Repository;

use App\Domain\CRM\Entity\Manager;
use App\Domain\CRM\Repository\ManagerRepository;
use App\Infrastructure\CRM\Mapper\ManagerMapper;
use App\Infrastructure\CRM\Model\ManagerModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentManagerRepository implements ManagerRepository
{
    public function __construct(
        private ManagerMapper $mapper,
    ) {}

    public function save(Manager $manager): void
    {
        $existing = ManagerModel::query()->find($manager->id()->value);
        $this->mapper->toPersistence($manager, $existing)->save();
    }

    public function findById(EntityId $id): ?Manager
    {
        $model = ManagerModel::query()->find($id->value);

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function getById(EntityId $id): Manager
    {
        return $this->findById($id)
            ?? throw new DomainException('Manager not found.');
    }

    public function findByEmail(string $email): ?Manager
    {
        $model = ManagerModel::query()
            ->where('email', strtolower(trim($email)))
            ->first();

        return $model !== null ? $this->mapper->toDomain($model) : null;
    }

    public function emailExists(string $email, ?EntityId $exceptId = null): bool
    {
        $query = ManagerModel::query()->where('email', strtolower(trim($email)));

        if ($exceptId !== null) {
            $query->where('id', '!=', $exceptId->value);
        }

        return $query->exists();
    }

    public function delete(EntityId $id): void
    {
        ManagerModel::query()->whereKey($id->value)->delete();
    }
}
