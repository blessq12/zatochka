<?php

namespace App\Infrastructure\Equipment\ReadModel;

use App\Application\Equipment\DTO\ClientEquipmentDTO;
use App\Application\Equipment\ReadPort\EquipmentReadPort;
use App\Infrastructure\Equipment\Mapper\ClientEquipmentMapper;
use App\Infrastructure\Equipment\Model\ClientEquipmentModel;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;
use App\Infrastructure\Workshop\Presenter\MasterProductionTaskPresenter;

final readonly class EloquentEquipmentReadModel implements EquipmentReadPort
{
    public function __construct(
        private ClientEquipmentMapper $mapper,
        private MasterProductionTaskPresenter $taskPresenter,
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

    public function orderHistory(int $equipmentId): array
    {
        $itemIds = OrderItemModel::query()
            ->where('client_equipment_id', $equipmentId)
            ->pluck('id')
            ->all();

        if ($itemIds === []) {
            return [];
        }

        return ProductionTaskModel::query()
            ->with([
                'comments',
                'orderItem.order.client',
                'orderItem.equipment.components',
            ])
            ->whereIn('order_item_id', $itemIds)
            ->orderByDesc('id')
            ->get()
            ->map(function ($model) {
                $card = $this->taskPresenter->present($model);

                return [
                    'id' => $card->id,
                    'order_number' => $card->orderNumber,
                    'status' => $card->posStatus,
                    'works' => $card->works,
                    'internal_notes' => $card->internalNotes,
                    'created_at' => $card->createdAt,
                    'service_type' => $card->serviceType,
                ];
            })
            ->all();
    }
}
