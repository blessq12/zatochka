<?php

namespace App\Application\Inventory\ReadPort;

use App\Application\Inventory\DTO\StockItemDTO;

interface StockReadPort
{
    public function findById(int $stockItemId): ?StockItemDTO;

    public function findByMaterialId(int $materialId): ?StockItemDTO;
}
