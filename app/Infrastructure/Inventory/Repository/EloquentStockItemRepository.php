<?php

namespace App\Infrastructure\Inventory\Repository;

use App\Domain\Inventory\Entity\StockItem;
use App\Domain\Inventory\Repository\StockItemRepository;
use App\Infrastructure\Inventory\Mapper\StockItemMapper;
use App\Infrastructure\Inventory\Model\MaterialModel;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Facades\DB;

final readonly class EloquentStockItemRepository implements StockItemRepository
{
    public function __construct(
        private StockItemMapper $mapper,
    ) {}

    public function save(StockItem $stockItem): void
    {
        DB::transaction(function () use ($stockItem): void {
            $material = $this->mapper->materialToPersistence($stockItem);
            MaterialModel::query()->updateOrCreate(
                ['id' => $material->id],
                [
                    'sku' => $material->sku,
                    'name' => $material->name,
                    'unit' => $material->unit,
                    'category' => $material->category,
                ],
            );

            $model = StockItemModel::query()->find($stockItem->id()->value);
            $model = $this->mapper->toPersistence($stockItem, $model);
            $model->save();

            WarehouseMovementModel::query()->where('stock_item_id', $stockItem->id()->value)->delete();

            foreach ($this->mapper->movementsToPersistence($stockItem) as $row) {
                $row->save();
            }
        });
    }

    public function findById(EntityId $id): ?StockItem
    {
        $model = StockItemModel::query()->with(['material', 'movements'])->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): StockItem
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Stock item %d not found.', $id->value));
    }

    public function findByMaterialId(EntityId $materialId): ?StockItem
    {
        $model = StockItemModel::query()
            ->with(['material', 'movements'])
            ->where('material_id', $materialId->value)
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }
}
