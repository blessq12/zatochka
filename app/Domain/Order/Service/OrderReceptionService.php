<?php

namespace App\Domain\Order\Service;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\ReceptionData;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class OrderReceptionService
{
    /**
     * @param array<int, ReceptionData> $receptionByItemId keyed by OrderItem id value
     */
    public function applyReception(Order $order, array $receptionByItemId): void
    {
        foreach ($order->items() as $item) {
            $reception = $receptionByItemId[$item->id()->value] ?? null;

            if ($reception === null) {
                throw new DomainException(sprintf(
                    'Reception data is missing for order item %d.',
                    $item->id()->value,
                ));
            }

            $item->completeReception($reception);
        }

        $order->completeReception();
    }

    public function applyItemReception(Order $order, EntityId $orderItemId, ReceptionData $receptionData): void
    {
        $order->item($orderItemId)->completeReception($receptionData);
    }
}
