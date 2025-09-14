<?php

namespace App\Infrastructure\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockMovement;
use App\Domain\Warehouse\Repository\StockMovementRepository;
use App\Domain\Warehouse\Mapper\StockMovementMapper;
use App\Models\StockMovement as StockMovementModel;

class StockMovementRepositoryImpl implements StockMovementRepository
{
    public function __construct(
        private StockMovementMapper $mapper
    ) {}

    public function create(array $data): StockMovement
    {
        $model = StockMovementModel::create($data);
        return $this->mapper->toDomain($model);
    }

    public function get(int $id): ?StockMovement
    {
        $model = StockMovementModel::find($id);
        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function getByStockItem(int $stockItemId): array
    {
        return StockMovementModel::where('stock_item_id', $stockItemId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getByStockItemAndType(int $stockItemId, string $movementType): array
    {
        return StockMovementModel::where('stock_item_id', $stockItemId)
            ->where('movement_type', $movementType)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getRecentByStockItem(int $stockItemId, int $limit = 10): array
    {
        return StockMovementModel::where('stock_item_id', $stockItemId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getByOrder(int $orderId): array
    {
        return StockMovementModel::where('order_id', $orderId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getByUser(int $userId): array
    {
        return StockMovementModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getByDateRange(\DateTime $from, \DateTime $to): array
    {
        return StockMovementModel::whereBetween('created_at', [$from, $to])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($model) => $this->mapper->toDomain($model))
            ->toArray();
    }

    public function getTotalByStockItem(int $stockItemId, ?string $movementType = null): int
    {
        $query = StockMovementModel::where('stock_item_id', $stockItemId);

        if ($movementType) {
            $query->where('movement_type', $movementType);
        }

        return $query->sum('quantity');
    }
}
