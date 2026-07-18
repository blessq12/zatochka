<?php

namespace App\Filament\Inventory\Support;

use App\Application\Inventory\ReadPort\OrderMaterialWriteOffReadPort;
use App\Infrastructure\Inventory\Model\WarehouseMovementModel;

/**
 * Active (non-reversed) order write-offs for Filament forms and pricing.
 */
final class OrderMaterialWriteOffs
{
    /**
     * @return list<\App\Application\Inventory\DTO\OrderMaterialWriteOffLineDTO>
     */
    public static function activeWriteOffs(string $orderId): array
    {
        return app(OrderMaterialWriteOffReadPort::class)->listActiveByOrderId($orderId);
    }

    /**
     * @return array<int, string> movement_id => label
     */
    public static function options(string $orderId): array
    {
        $options = [];

        foreach (self::activeWriteOffs($orderId) as $line) {
            $name = $line->materialName ?? ('Материал #'.$line->stockItemId);
            $options[$line->movementId] = sprintf(
                '%s · qty %s · %s ₽ [#%d]',
                $name,
                $line->quantity,
                $line->unitPrice,
                $line->movementId,
            );
        }

        return $options;
    }

    public static function findActive(string $orderId, int $movementId): ?WarehouseMovementModel
    {
        foreach (self::activeWriteOffs($orderId) as $line) {
            if ($line->movementId === $movementId) {
                return WarehouseMovementModel::query()->find($movementId);
            }
        }

        return null;
    }

    /**
     * @return list<int>
     */
    public static function reversedWriteOffIds(string $orderId): array
    {
        $activeIds = array_fill_keys(
            array_map(static fn ($line): int => $line->movementId, self::activeWriteOffs($orderId)),
            true,
        );

        return WarehouseMovementModel::query()
            ->where('order_id', $orderId)
            ->where('type', \App\Domain\Inventory\VO\MovementType::WriteOff->value)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->filter(static fn (int $id): bool => ! isset($activeIds[$id]))
            ->values()
            ->all();
    }
}
