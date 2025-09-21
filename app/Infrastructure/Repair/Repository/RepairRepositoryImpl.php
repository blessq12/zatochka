<?php

namespace App\Infrastructure\Repair\Repository;

use App\Domain\Repair\Entity\Repair;
use App\Domain\Repair\Mapper\RepairMapper;
use App\Domain\Repair\Repository\RepairRepository;
use App\Models\Repair as RepairModel;

class RepairRepositoryImpl implements RepairRepository
{
    public function __construct(
        private RepairMapper $repairMapper
    ) {}

    public function create(array $data): Repair
    {
        $model = RepairModel::create($data);
        return $this->repairMapper->toDomain($model);
    }

    public function get(int $id): ?Repair
    {
        $model = RepairModel::find($id);
        return $model ? $this->repairMapper->toDomain($model) : null;
    }

    public function update(Repair $repair, array $data): Repair
    {
        $model = RepairModel::findOrFail($repair->getId());
        $model->update($data);
        return $this->repairMapper->toDomain($model->fresh());
    }

    public function delete(int $id): bool
    {
        return RepairModel::where('id', $id)->update(['is_deleted' => true]) > 0;
    }

    public function exists(int $id): bool
    {
        return RepairModel::where('id', $id)->exists();
    }

    public function getAll(): array
    {
        $models = RepairModel::all();
        return $models->map(fn($model) => $this->repairMapper->toDomain($model))->toArray();
    }

    public function getByOrderId(int $orderId): array
    {
        $models = RepairModel::where('order_id', $orderId)->get();
        return $models->map(fn($model) => $this->repairMapper->toDomain($model))->toArray();
    }

    public function getByMasterId(int $masterId): array
    {
        $models = RepairModel::where('master_id', $masterId)->get();
        return $models->map(fn($model) => $this->repairMapper->toDomain($model))->toArray();
    }

    public function getByStatus(string $status): array
    {
        $models = RepairModel::where('status', $status)->get();
        return $models->map(fn($model) => $this->repairMapper->toDomain($model))->toArray();
    }

    public function getActive(): array
    {
        $models = RepairModel::where('status', '!=', 'cancelled')->get();
        return $models->map(fn($model) => $this->repairMapper->toDomain($model))->toArray();
    }

    public function getOverdue(): array
    {
        $models = RepairModel::where('estimated_completion', '<', now())
            ->where('status', '!=', 'completed')
            ->get();
        return $models->map(fn($model) => $this->repairMapper->toDomain($model))->toArray();
    }
}
