<?php

namespace App\Infrastructure\Equipment\Persistence\Repository;

use App\Domain\Equipment\Entity\Equipment;
use App\Domain\Equipment\Repository\EquipmentRepositoryInterface;
use App\Infrastructure\Equipment\Persistence\Eloquent\EquipmentModel;
use App\Infrastructure\Equipment\Persistence\Mapper\EquipmentMapper;

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
