<?php

namespace App\Infrastructure\Persistence\Mappers\Equipment;

use App\Domain\Equipment\Entities\Equipment;
use App\Infrastructure\Persistence\Eloquent\Models\Equipment\EquipmentModel;

final class EquipmentMapper
{
    public function toDomain(EquipmentModel $model): Equipment
    {
        return new Equipment(
            id: $model->id,
            name: $model->name,
            brand: $model->brand,
            model: $model->model,
            serialNumbers: $model->serial_numbers ?? [],
        );
    }

    public function fillModel(Equipment $equipment, EquipmentModel $model): void
    {
        $model->fill([
            'name' => $equipment->name(),
            'brand' => $equipment->brand(),
            'model' => $equipment->model(),
            'serial_numbers' => $equipment->serialNumbers(),
        ]);
    }
}
