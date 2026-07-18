<?php

namespace App\Infrastructure\Inventory\ReadModel;

use App\Application\Inventory\DTO\OrderMaterialWriteOffLineDTO;
use App\Application\Inventory\ReadPort\OrderMaterialWriteOffReadPort;
use App\Domain\Inventory\VO\MovementType;
use App\Infrastructure\Inventory\Model\StockItemModel;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;

final readonly class EloquentOrderMaterialWriteOffReadModel implements OrderMaterialWriteOffReadPort
{
    public function listActiveByOrderId(string $orderId): array
    {
        $movements = WarehouseMovementModel::query()
            ->where('order_id', $orderId)
            ->where('type', MovementType::WriteOff->value)
            ->orderBy('id')
            ->get();

        $reversedIds = WarehouseMovementModel::query()
            ->where('order_id', $orderId)
            ->where('type', MovementType::Reversal->value)
            ->whereNotNull('reverses_movement_id')
            ->pluck('reverses_movement_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();

        $reversedLookup = array_fill_keys($reversedIds, true);
        $lines = [];

        foreach ($movements as $movement) {
            if (isset($reversedLookup[(int) $movement->id])) {
                continue;
            }

            $stockItem = StockItemModel::query()
                ->with('material')
                ->find($movement->stock_item_id);

            $lines[] = new OrderMaterialWriteOffLineDTO(
                (int) $movement->id,
                (int) $movement->stock_item_id,
                (string) $movement->quantity,
                number_format((float) ($movement->unit_price ?? 0), 2, '.', ''),
                (string) ($movement->currency ?: 'RUB'),
                $movement->order_item_id !== null ? (int) $movement->order_item_id : null,
                $movement->comment !== null ? (string) $movement->comment : null,
                $stockItem?->material?->name,
            );
        }

        return $lines;
    }
}
