<?php

namespace App\Infrastructure\Persistence\Repositories\Warehouse;

use App\Domain\Warehouse\Entities\StockMovement;
use App\Domain\Warehouse\Repositories\StockMovementRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Warehouse\StockMovementModel;
use App\Infrastructure\Persistence\Mappers\Warehouse\StockMovementMapper;

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
