<?php

namespace App\Domain\Warehouse\Repository;

use App\Domain\Warehouse\Entity\Warehouse;

interface WarehouseRepository
{
    public function create(array $data): Warehouse;

    public function get(int $id): ?Warehouse;

    public function update(Warehouse $warehouse, array $data): Warehouse;

    public function delete(int $id): bool;

    public function exists(int $id): bool;

    public function findByBranch(int $branchId): array;

    public function findActiveByBranch(int $branchId): array;

    public function existsByNameInBranch(string $name, int $branchId, ?int $excludeId = null): bool;
}
