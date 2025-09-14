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
}
