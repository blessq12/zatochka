<?php

namespace App\Infrastructure\Inventory\ReadModel;

use App\Application\Inventory\DTO\StockItemDTO;
use App\Application\Inventory\ReadPort\StockReadPort;
use App\Infrastructure\Inventory\Mapper\StockItemMapper;
use App\Infrastructure\Inventory\Model\StockItemModel;

final readonly class EloquentStockReadModel implements StockReadPort
{
    public function __construct(
        private StockItemMapper $mapper,
    ) {}

    public function findById(int $stockItemId): ?StockItemDTO
    {
        $model = StockItemModel::query()->with('material')->find($stockItemId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function findByMaterialId(int $materialId): ?StockItemDTO
    {
        $model = StockItemModel::query()
            ->with('material')
            ->where('material_id', $materialId)
            ->first();

        return $model === null ? null : $this->mapper->toDTO($model);
    }
}
