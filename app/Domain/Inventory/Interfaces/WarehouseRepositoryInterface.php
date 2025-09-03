<?php

namespace App\Domain\Inventory\Interfaces;

use App\Domain\Inventory\Entities\Warehouse;
use App\Domain\Inventory\ValueObjects\WarehouseId;
use App\Domain\Inventory\ValueObjects\BranchId;

interface WarehouseRepositoryInterface
{
    public function findById(WarehouseId $id): ?Warehouse;

    public function findByBranchId(BranchId $branchId): ?Warehouse;

    public function findAll(): array;

    public function findActive(): array;

    public function save(Warehouse $warehouse): void;

    public function delete(WarehouseId $id): void;

    public function exists(WarehouseId $id): bool;

    public function existsByBranchId(BranchId $branchId): bool;
}
