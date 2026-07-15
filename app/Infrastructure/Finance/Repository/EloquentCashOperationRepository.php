<?php

namespace App\Infrastructure\Finance\Repository;

use App\Domain\Finance\Entity\CashOperation;
use App\Domain\Finance\Repository\CashOperationRepository;
use App\Infrastructure\Finance\Mapper\CashOperationMapper;
use App\Infrastructure\Finance\Model\CashOperationModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final readonly class EloquentCashOperationRepository implements CashOperationRepository
{
    public function __construct(
        private CashOperationMapper $mapper,
    ) {}

    public function save(CashOperation $operation): void
    {
        $model = CashOperationModel::query()->find($operation->id()->value);
        $model = $this->mapper->toPersistence($operation, $model);
        $model->save();
    }

    public function findById(EntityId $id): ?CashOperation
    {
        $model = CashOperationModel::query()->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): CashOperation
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Cash operation %d not found.', $id->value));
    }
}
