<?php

namespace App\Domain\Crm\Repository;

use App\Domain\Crm\Aggregate\Equipment;

interface EquipmentRepository
{
    public function save(Equipment $equipment): Equipment;

    public function findById(int $id): ?Equipment;

    /**
     * @return list<Equipment>
     */
    public function all(?int $clientId = null, ?string $query = null): array;

    public function delete(Equipment $equipment): void;
}
