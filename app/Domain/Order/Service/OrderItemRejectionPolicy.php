<?php

namespace App\Domain\Order\Service;

use App\Domain\Order\VO\OrderItemStatus;

/**
 * Единая формула «сколько единиц позиции осталось к выдаче» и «отклонена ли позиция целиком».
 * Используется ACL-портами Workshop/Pricing и презентерами вместо копий формулы.
 */
final class OrderItemRejectionPolicy
{
    public static function repairableQuantity(?int $quantity, int $rejectedQuantity, string $status): int
    {
        if ($quantity !== null && $quantity > 0) {
            return max(0, $quantity - $rejectedQuantity);
        }

        return $status === OrderItemStatus::Rejected->value ? 0 : 1;
    }

    public static function isFullyRejected(?int $quantity, int $rejectedQuantity, string $status): bool
    {
        return $status === OrderItemStatus::Rejected->value
            || self::repairableQuantity($quantity, $rejectedQuantity, $status) === 0;
    }
}
