<?php

namespace App\Domain\Inventory\Interfaces;

use App\Domain\Inventory\Entities\Warehouse;
// ... existing code ...

interface WarehouseRepositoryInterface
{
    public function findById(int $id): ?Warehouse;

    public function findByBranchId(int $branchId): ?Warehouse;

    public function findAll(): array;

    public function findActive(): array;

    public function save(Warehouse $warehouse): void;

    public function delete(int $id): void;

    public function exists(int $id): bool;

    public function existsByBranchId(int $branchId): bool;
}
