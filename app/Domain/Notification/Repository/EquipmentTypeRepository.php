<?php

namespace App\Domain\Notification\Repository;

use App\Domain\Notification\Entity\EquipmentType;

interface EquipmentTypeRepository
{
    public function create(array $data): EquipmentType;
    public function get(int $id): ?EquipmentType;
    public function update(EquipmentType $equipmentType, array $data): EquipmentType;
    public function delete(int $id): bool;
    public function exists(int $id): bool;
    public function getAll(): array;
}
