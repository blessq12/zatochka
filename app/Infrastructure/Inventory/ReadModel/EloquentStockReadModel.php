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

    public function search(?string $query, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $builder = StockItemModel::query()->with('material');

        if ($query !== null && trim($query) !== '') {
            $term = '%'.trim($query).'%';
            $builder->whereHas('material', function ($q) use ($term): void {
                $q->where('name', 'like', $term)
                    ->orWhere('sku', 'like', $term)
                    ->orWhere('category', 'like', $term);
            });
        }

        $total = (clone $builder)->count();
        $items = $builder
            ->orderBy('id')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn ($model) => $this->mapper->toDTO($model))
            ->all();

        return [
            'items' => $items,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ],
        ];
    }
}
