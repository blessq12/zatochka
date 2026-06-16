<?php

namespace App\Infrastructure\Persistence\Repositories\Equipment;

use App\Domain\Equipment\Entities\Equipment;
use App\Domain\Equipment\Repositories\EquipmentRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Models\Equipment\EquipmentModel;
use App\Infrastructure\Persistence\Mappers\Equipment\EquipmentMapper;

final class EloquentEquipmentRepository implements EquipmentRepositoryInterface
{
    public function __construct(
        private EquipmentMapper $mapper,
    ) {}

    public function findById(int $id): ?Equipment
    {
        $model = EquipmentModel::query()->find($id);

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function save(Equipment $equipment): Equipment
    {
        $model = $equipment->id() !== null
            ? EquipmentModel::query()->findOrFail($equipment->id())
            : new EquipmentModel;

        $this->mapper->fillModel($equipment, $model);
        $model->save();

        return $this->mapper->toDomain($model);
    }
}
