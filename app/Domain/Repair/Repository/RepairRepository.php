<?php

namespace App\Domain\Repair\Repository;

use App\Domain\Repair\Entity\Repair;

interface RepairRepository
{
    public function create(array $data): Repair;
    public function get(int $id): ?Repair;
    public function update(Repair $repair, array $data): Repair;
    public function delete(int $id): bool;
    public function exists(int $id): bool;
    public function getAll(): array;
    public function getByOrderId(int $orderId): array;
    public function getByMasterId(int $masterId): array;
    public function getByStatus(string $status): array;
    public function getActive(): array;
    public function getOverdue(): array;
}
