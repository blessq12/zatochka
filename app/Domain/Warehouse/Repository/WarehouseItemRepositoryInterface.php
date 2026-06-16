<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Entity\WarehouseItem;

interface WarehouseItemRepositoryInterface
{
    public function findById(int $id): ?WarehouseItem;

    public function save(WarehouseItem $item): WarehouseItem;

    /**
     * @return array{items: list<WarehouseItem>, total: int}
     */
    public function search(?string $query, int $page, int $perPage): array;
}
