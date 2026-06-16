<?php

namespace App\Domain\Equipment\Repository;

use App\Domain\Equipment\Entity\Equipment;

interface EquipmentRepositoryInterface
{
    public function findById(int $id): ?Equipment;

    public function save(Equipment $equipment): Equipment;
}
