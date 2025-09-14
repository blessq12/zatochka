<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Entity\StockMovement;

interface StockMovementRepository
{
    public function create(array $data): StockMovement;

    public function get(int $id): ?StockMovement;

    public function getByStockItem(int $stockItemId): array;

    public function getByStockItemAndType(int $stockItemId, string $movementType): array;

    public function getRecentByStockItem(int $stockItemId, int $limit = 10): array;

    public function getByOrder(int $orderId): array;

    public function getByUser(int $userId): array;

    public function getByDateRange(\DateTime $from, \DateTime $to): array;

    public function getTotalByStockItem(int $stockItemId, string $movementType = null): int;
}
