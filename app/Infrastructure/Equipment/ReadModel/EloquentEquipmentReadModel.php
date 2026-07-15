<?php

namespace App\Infrastructure\Equipment\ReadModel;

use App\Application\Equipment\DTO\ClientEquipmentDTO;
use App\Application\Equipment\ReadPort\EquipmentReadPort;
use App\Infrastructure\Equipment\Mapper\ClientEquipmentMapper;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;

final readonly class EloquentEquipmentReadModel implements EquipmentReadPort
{
    public function __construct(
        private ClientEquipmentMapper $mapper,
    ) {}

    public function findById(int $equipmentId): ?ClientEquipmentDTO
    {
        $model = ClientEquipmentModel::query()->with(['components', 'repairHistory'])->find($equipmentId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }

    public function listByClientId(int $clientId): array
    {
        return ClientEquipmentModel::query()
            ->with(['components', 'repairHistory'])
            ->where('client_id', $clientId)
            ->get()
            ->map(fn ($model) => $this->mapper->toDTO($model))
            ->all();
    }
}
