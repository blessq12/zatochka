<?php

namespace App\Domain\Warehouse\Repositories;

use App\Domain\Warehouse\Entities\WarehouseItem;

interface WarehouseItemRepositoryInterface
{
    public function findById(int $id): ?WarehouseItem;

    public function save(WarehouseItem $item): WarehouseItem;
}
