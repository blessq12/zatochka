<?php

namespace App\Domain\Inventory\Service;

use App\Domain\Inventory\Entity\StockItem;
use App\Domain\Inventory\VO\Quantity;
use App\Shared\ValueObject\EntityId;

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
    ): void {
        $stockItem->writeOff($movementId, $quantity, $comment, $orderId, $orderItemId);
    }

    public function changeBalance(StockItem $stockItem, EntityId $movementId, Quantity $quantity, ?string $comment = null): void
    {
        $stockItem->adjust($movementId, $quantity, $comment);
    }
}
