<?php

namespace App\Infrastructure\Workshop\Port;

use App\Application\Workshop\DTO\OrderProductionContextDTO;
use App\Application\Workshop\DTO\OrderProductionItemDTO;
use App\Application\Workshop\Port\OrderProductionContextPort;
use App\Domain\Order\Service\OrderItemRejectionPolicy;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Shared\Domain\DomainException;

final readonly class EloquentOrderProductionContextPort implements OrderProductionContextPort
{
    public function getById(string $orderId): OrderProductionContextDTO
    {
        $order = OrderModel::query()->find($orderId);

        if ($order === null) {
            throw new DomainException('Order not found.');
        }

        $items = OrderItemModel::query()
            ->where('order_id', $orderId)
            ->orderBy('id')
            ->get()
            ->map(static fn (OrderItemModel $item): OrderProductionItemDTO => new OrderProductionItemDTO(
                (int) $item->id,
                $item->client_equipment_id !== null ? (int) $item->client_equipment_id : null,
                OrderItemRejectionPolicy::isFullyRejected(
                    (int) $item->quantity,
                    (int) $item->rejected_quantity,
                    (string) $item->status,
                ),
            ))
            ->values()
            ->all();

        return new OrderProductionContextDTO(
            (string) $order->id,
            (string) $order->service_type,
            (string) $order->status,
            $items,
        );
    }
}
