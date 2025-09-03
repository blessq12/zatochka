<?php

namespace App\Domain\Inventory\Interfaces;

use App\Domain\Inventory\Entities\StockMovement;
use App\Domain\Inventory\ValueObjects\StockItemId;
use App\Domain\Inventory\ValueObjects\WarehouseId;
use App\Domain\Shared\ValueObjects\UuidValueObject;
use App\Domain\Inventory\ValueObjects\MovementType;

interface StockMovementRepositoryInterface
{
    public function findById(UuidValueObject $id): ?StockMovement;

    public function findByStockItemId(StockItemId $stockItemId): array;

    public function findByWarehouseId(WarehouseId $warehouseId): array;

    public function findByOrderId(UuidValueObject $orderId): array;

    public function findByRepairId(UuidValueObject $repairId): array;

    public function findByMovementType(MovementType $movementType): array;

    public function findByDateRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array;

    public function findByStockItemAndDateRange(StockItemId $stockItemId, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): array;

    public function findRecentMovements(int $limit = 100): array;

    public function findAll(): array;

    public function save(StockMovement $movement): void;

    public function delete(UuidValueObject $id): void;

    public function exists(UuidValueObject $id): bool;

    public function getMovementHistory(StockItemId $stockItemId, int $limit = 50): array;
}
