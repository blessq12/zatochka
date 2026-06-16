<?php

namespace App\Domain\Equipment\Repository;

use App\Domain\Equipment\Entity\Equipment;

interface EquipmentRepositoryInterface
{
    public function findById(int $id): ?Equipment;

    public function save(Equipment $equipment): Equipment;

    public function findBySerialNumber(string $serial): ?Equipment;

    /**
     * @return array{items: list<Equipment>, total: int}
     */
    public function search(?string $query, int $page, int $perPage): array;
}
