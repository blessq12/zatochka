<?php

namespace App\Infrastructure\Crm\Repository;

use App\Domain\Crm\Aggregate\Equipment;
use App\Domain\Crm\Entity\EquipmentModule;
use App\Domain\Crm\Repository\EquipmentRepository;
use App\Infrastructure\Crm\Eloquent\EquipmentModel;
use App\Infrastructure\Crm\Eloquent\EquipmentModuleModel;
use Illuminate\Support\Facades\DB;

final class EloquentEquipmentRepository implements EquipmentRepository
{
    public function save(Equipment $equipment): Equipment
    {
        return DB::transaction(function () use ($equipment): Equipment {
            /** @var EquipmentModel $model */
            $model = $equipment->id() === null
                ? new EquipmentModel()
                : EquipmentModel::withTrashed()->findOrFail($equipment->id());

            $model->client_id = $equipment->clientId();
            $model->name = $equipment->name();
            $model->brand = $equipment->brand();
            $model->type = $equipment->type();
            $model->save();

            if ($equipment->id() === null) {
                $equipment->assignId((int) $model->id);
            }

            if ($equipment->isDeleted()) {
                EquipmentModuleModel::query()->where('equipment_id', $model->id)->delete();
                if ($model->deleted_at === null) {
                    $model->delete();
                }

                return $equipment;
            }

            EquipmentModuleModel::query()->where('equipment_id', $model->id)->delete();

            foreach ($equipment->modules() as $module) {
                $moduleModel = new EquipmentModuleModel([
                    'equipment_id' => $model->id,
                    'name' => $module->name(),
                    'serial_number' => $module->serialNumber(),
                ]);
                $moduleModel->save();
                $module->assignId((int) $moduleModel->id);
            }

            return $equipment;
        });
    }

    public function findById(int $id): ?Equipment
    {
        /** @var EquipmentModel|null $model */
        $model = EquipmentModel::query()->with('modules')->find($id);

        if ($model === null) {
            return null;
        }

        return $this->toDomain($model);
    }

    public function all(?int $clientId = null, ?string $query = null): array
    {
        $builder = EquipmentModel::query()->with('modules')->orderBy('id');

        if ($clientId !== null) {
            $builder->where('client_id', $clientId);
        }

        $q = $query !== null ? trim($query) : '';
        if ($q !== '') {
            $like = '%'.$q.'%';
            $builder->where(static function ($inner) use ($like): void {
                $inner->where('name', 'like', $like)
                    ->orWhere('brand', 'like', $like)
                    ->orWhere('type', 'like', $like)
                    ->orWhereHas('modules', static function ($modules) use ($like): void {
                        $modules->where('name', 'like', $like)
                            ->orWhere('serial_number', 'like', $like);
                    });
            });
        }

        return $builder
            ->get()
            ->map(fn (EquipmentModel $model): Equipment => $this->toDomain($model))
            ->values()
            ->all();
    }

    public function delete(Equipment $equipment): void
    {
        $equipment->markDeleted();
        $this->save($equipment);
    }

    private function toDomain(EquipmentModel $model): Equipment
    {
        $modules = $model->modules
            ->map(static fn (EquipmentModuleModel $module): EquipmentModule => new EquipmentModule(
                (int) $module->id,
                (string) $module->name,
                (string) $module->serial_number,
            ))
            ->values()
            ->all();

        return new Equipment(
            (int) $model->id,
            (int) $model->client_id,
            (string) $model->name,
            (string) $model->brand,
            (string) $model->type,
            $modules,
            false,
        );
    }
}
