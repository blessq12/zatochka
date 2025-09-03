<?php

namespace App\Domain\Inventory\Interfaces;

use App\Domain\Inventory\Entities\StockMovement;
// ... existing code ...
use App\Domain\Inventory\ValueObjects\MovementType;

interface StockMovementRepositoryInterface
{
    public function findById(int $id): ?StockMovement;

    public function findByStockItemId(int $stockItemId): array;

    public function findByWarehouseId(int $warehouseId): array;

    public function findByOrderId(int $orderId): array;

    public function findByRepairId(int $repairId): array;

    public function findByMovementType(MovementType $movementType): array;

    public function findByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array;

    public function findByStockItemAndDateRange(int $stockItemId, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array;

    public function findRecentMovements(int $limit = 100): array;

    public function findAll(): array;

    public function save(StockMovement $movement): void;

    public function delete(int $id): void;

    public function exists(int $id): bool;

    public function getMovementHistory(int $stockItemId, int $limit = 50): array;
}
