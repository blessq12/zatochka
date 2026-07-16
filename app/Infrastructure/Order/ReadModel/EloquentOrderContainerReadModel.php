<?php

namespace App\Infrastructure\Order\ReadModel;

use App\Application\Order\DTO\OrderContainerDTO;
use App\Application\Order\DTO\OrderContainerItemDTO;
use App\Application\Order\ReadPort\OrderContainerReadPort;
use App\Application\Pricing\ReadPort\WorkPriceReadPort;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;
use App\Infrastructure\Order\Mapper\OrderMapper;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Workshop\Model\PerformedWorkModel;
use App\Infrastructure\Workshop\Model\ProductionTaskModel;

final readonly class EloquentOrderContainerReadModel implements OrderContainerReadPort
{
    public function __construct(
        private OrderMapper $orderMapper,
        private WorkPriceReadPort $workPrices,
    ) {}

    public function findById(string $orderId): ?OrderContainerDTO
    {
        $model = OrderModel::query()
            ->with(['items.equipment.components', 'items.reception', 'client'])
            ->find($orderId);

        if ($model === null) {
            return null;
        }

        $orderDto = $this->orderMapper->toDTO($model);

        $task = ProductionTaskModel::query()
            ->where('order_id', $orderId)
            ->first();

        $productionTask = $task === null ? null : [
            'id' => (int) $task->id,
            'status' => (string) $task->status,
            'master_id' => $task->master_id !== null ? (int) $task->master_id : null,
        ];

        $works = $task === null
            ? collect()
            : PerformedWorkModel::query()
                ->where('production_task_id', $task->id)
                ->orderBy('id')
                ->get();

        $movements = WarehouseMovementModel::query()
            ->where('order_id', $orderId)
            ->orderBy('id')
            ->get();

        $masterInternalComments = [];

        foreach ($task?->master_comments ?? [] as $comment) {
            if (! is_array($comment)) {
                continue;
            }

            $masterInternalComments[] = [
                'id' => (int) $comment['id'],
                'text' => (string) $comment['text'],
                'created_at' => (string) ($comment['created_at'] ?? ''),
            ];
        }

        $items = [];

        foreach ($model->items as $item) {
            $itemId = (int) $item->id;
            $quantity = $item->quantity !== null ? (int) $item->quantity : null;
            $rejectedQuantity = (int) ($item->rejected_quantity ?? 0);
            $status = (string) $item->status;
            $repairableQuantity = $quantity !== null
                ? max(0, $quantity - $rejectedQuantity)
                : ($status === 'rejected' ? 0 : 1);

            $itemWorks = [];
            $unitAmountSum = 0.0;
            $currency = (string) ($model->estimated_currency ?: 'RUB');
            $allWorksPriced = true;
            $hasWorks = false;

            $componentNames = [];

            foreach ($item->equipment?->components ?? [] as $component) {
                $componentNames[(int) $component->id] = (string) $component->name;
            }

            foreach ($works as $work) {
                if ((int) $work->order_item_id !== $itemId) {
                    continue;
                }

                $hasWorks = true;
                $workPrice = $this->workPrices->findByPerformedWorkId((int) $work->id);
                $price = null;

                if ($workPrice !== null && $workPrice->calculated) {
                    $unitAmount = (float) $workPrice->baseAmount;
                    $unitAmountSum += $unitAmount;
                    $currency = $workPrice->currency;
                    $price = [
                        'unit_amount' => $workPrice->baseAmount,
                        'line_amount' => (string) round($unitAmount * $repairableQuantity, 2),
                        'currency' => $workPrice->currency,
                        'calculated' => true,
                    ];
                } else {
                    $allWorksPriced = false;
                }

                $componentId = $work->equipment_component_id !== null
                    ? (int) $work->equipment_component_id
                    : null;

                $itemWorks[] = [
                    'id' => (int) $work->id,
                    'description' => (string) $work->description,
                    'created_at' => $work->created_at?->toIso8601String() ?? '',
                    'order_item_id' => (int) $work->order_item_id,
                    'equipment_component_id' => $componentId,
                    'component_name' => $componentId !== null
                        ? ($componentNames[$componentId] ?? null)
                        : null,
                    'price' => $price,
                ];
            }

            $estimate = null;

            if ($hasWorks && $allWorksPriced && $unitAmountSum > 0) {
                $estimate = [
                    'id' => $itemId,
                    'unit_amount' => (string) $unitAmountSum,
                    'base_amount' => (string) $unitAmountSum,
                    'line_amount' => (string) round($unitAmountSum * $repairableQuantity, 2),
                    'currency' => $currency,
                    'calculated' => true,
                ];
            }

            $itemMaterials = $movements
                ->filter(static fn ($m) => $m->order_item_id === null || (int) $m->order_item_id === $itemId)
                ->map(static fn ($m) => [
                    'id' => (int) $m->id,
                    'stock_item_id' => (int) $m->stock_item_id,
                    'quantity' => (string) $m->quantity,
                    'comment' => $m->comment !== null ? (string) $m->comment : null,
                    'created_at' => $m->occurred_at?->toIso8601String(),
                ])
                ->values()
                ->all();

            $items[] = new OrderContainerItemDTO(
                $itemId,
                $item->client_equipment_id !== null ? (int) $item->client_equipment_id : null,
                $item->tool_name !== null ? (string) $item->tool_name : null,
                $item->tool_type !== null ? (string) $item->tool_type : null,
                $quantity,
                $rejectedQuantity,
                $repairableQuantity,
                $item->rejection_reason !== null ? (string) $item->rejection_reason : null,
                $status,
                $itemWorks,
                $itemMaterials,
                $estimate,
            );
        }

        return new OrderContainerDTO($orderDto, $productionTask, $items, $masterInternalComments);
    }
}
