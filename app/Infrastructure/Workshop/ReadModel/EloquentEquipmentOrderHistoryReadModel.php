<?php

namespace App\Infrastructure\Workshop\ReadModel;

use App\Application\Equipment\ReadPort\EquipmentOrderHistoryPort;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;
use App\Infrastructure\Workshop\Presenter\MasterProductionTaskPresenter;

final readonly class EloquentEquipmentOrderHistoryReadModel implements EquipmentOrderHistoryPort
{
    public function __construct(
        private MasterProductionTaskPresenter $taskPresenter,
    ) {}

    public function historyForEquipment(int $equipmentId): array
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
