<?php

namespace App\Application\Inventory\ReadPort;

use App\Application\Inventory\DTO\StockItemDTO;

interface StockReadPort
{
    public function findById(int $stockItemId): ?StockItemDTO;

    public function findByMaterialId(int $materialId): ?StockItemDTO;

    /**
     * @return array{items: list<StockItemDTO>, meta: array{total:int,page:int,per_page:int}}
     */
    public function search(?string $query, int $page = 1, int $perPage = 20): array;
}
