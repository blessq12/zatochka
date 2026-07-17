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

    public function search(?string $query, int $page = 1, int $perPage = 20): array
    {
        $page = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $builder = ClientEquipmentModel::query()->with(['components', 'repairHistory']);

        if ($query !== null && trim($query) !== '') {
            $term = '%'.trim($query).'%';
            $builder->where(function ($q) use ($term): void {
                $q->where('title', 'like', $term)
                    ->orWhere('brand', 'like', $term)
                    ->orWhere('model_name', 'like', $term)
                    ->orWhereHas('components', function ($cq) use ($term): void {
                        $cq->where('serial_number', 'like', $term)
                            ->orWhere('name', 'like', $term);
                    });
            });
        }

        $total = (clone $builder)->count();
        $items = $builder
            ->orderByDesc('id')
            ->forPage($page, $perPage)
            ->get()
            ->map(fn ($model) => $this->mapper->toDTO($model))
            ->all();

        return [
            'items' => $items,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'per_page' => $perPage,
            ],
        ];
    }
}
