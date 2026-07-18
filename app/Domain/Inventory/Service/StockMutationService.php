<?php

namespace App\Domain\Inventory\Service;

use App\Domain\Inventory\Entity\StockItem;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class StockMutationService
{
    public function receive(StockItem $stockItem, EntityId $movementId, Quantity $quantity, ?string $comment = null): void
    {
        $stockItem->receive($movementId, $quantity, $comment);
    }

    public function writeOff(
        StockItem $stockItem,
        EntityId $movementId,
        Quantity $quantity,
        ?string $comment = null,
        ?string $orderId = null,
        ?int $orderItemId = null,
        ?Money $unitPrice = null,
    ): void {
        $stockItem->writeOff($movementId, $quantity, $comment, $orderId, $orderItemId, $unitPrice);
    }

    public function reverseWriteOff(
        StockItem $stockItem,
        EntityId $reversalMovementId,
        EntityId $writeOffMovementId,
        ?string $comment = null,
    ): void {
        $stockItem->reverseWriteOff($reversalMovementId, $writeOffMovementId, $comment);
    }

    public function changeBalance(StockItem $stockItem, EntityId $movementId, Quantity $quantity, ?string $comment = null): void
    {
        $stockItem->adjust($movementId, $quantity, $comment);
    }
}
