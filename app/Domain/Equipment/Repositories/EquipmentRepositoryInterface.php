<?php

namespace App\Domain\Equipment\Repositories;

use App\Domain\Equipment\Entities\Equipment;

interface EquipmentRepositoryInterface
{
    public function findById(int $id): ?Equipment;

    public function save(Equipment $equipment): Equipment;
}
