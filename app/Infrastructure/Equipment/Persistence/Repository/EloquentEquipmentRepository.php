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

    public function findBySerialNumber(string $serial): ?Equipment
    {
        $model = EquipmentModel::query()
            ->whereRaw('CAST(serial_numbers AS TEXT) LIKE ?', ['%"'.$serial.'"%'])
            ->first();

        return $model ? $this->mapper->toDomain($model) : null;
    }

    public function search(?string $query, int $page, int $perPage): array
    {
        $builder = EquipmentModel::query();

        if ($query !== null && $query !== '') {
            $builder->where(function ($q) use ($query): void {
                $q->where('name', 'like', "%{$query}%")
                    ->orWhere('brand', 'like', "%{$query}%")
                    ->orWhere('model', 'like', "%{$query}%")
                    ->orWhereRaw('CAST(serial_numbers AS TEXT) LIKE ?', ['%'.$query.'%']);
            });
        }

        $builder->orderBy('name');

        $total = (clone $builder)->count();
        $models = $builder->forPage($page, $perPage)->get();

        return [
            'items' => $models->map(fn (EquipmentModel $model) => $this->mapper->toDomain($model))->all(),
            'total' => $total,
        ];
    }
}
