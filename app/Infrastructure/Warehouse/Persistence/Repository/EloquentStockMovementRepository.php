<?php

namespace App\Infrastructure\Warehouse\Persistence\Repository;

use App\Domain\Warehouse\Entity\StockMovement;
use App\Domain\Warehouse\Repository\StockMovementRepositoryInterface;
use App\Infrastructure\Warehouse\Persistence\Eloquent\StockMovementModel;
use App\Infrastructure\Warehouse\Persistence\Mapper\StockMovementMapper;

final class EloquentStockMovementRepository implements StockMovementRepositoryInterface
{
    public function __construct(
        private StockMovementMapper $mapper,
    ) {}

    public function save(StockMovement $movement): StockMovement
    {
        $model = $movement->id() !== null
            ? StockMovementModel::query()->findOrFail($movement->id())
            : new StockMovementModel;

        $this->mapper->fillModel($movement, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
