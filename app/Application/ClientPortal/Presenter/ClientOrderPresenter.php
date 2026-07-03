<?php

namespace App\Application\ClientPortal\Presenter;

use App\Domain\OrderFulfillment\Entity\Order;
use DateTimeInterface;

final class ClientOrderPresenter
{
    /**
     * ADR-005: без статусов цеха — только bucket active/history на уровне API.
     *
     * @param  list<int>  $reviewOrderIds
     */
    public static function listItem(Order $order, array $reviewOrderIds = []): array
    {
        $orderId = $order->id();

        return [
            'id' => $orderId,
            'order_number' => $order->orderNumber()->value,
            'service_types' => $order->serviceTypes(),
            'price' => $order->price(),
            'created_at' => $order->createdAt()?->format(DateTimeInterface::ATOM),
            'description' => $order->problemDescription(),
            'review_exists' => $orderId !== null && in_array($orderId, $reviewOrderIds, true),
        ];
    }

    /** @param list<Order> $orders */
    public static function list(array $orders, array $reviewOrderIds = []): array
    {
        return array_map(
            static fn (Order $order): array => self::listItem($order, $reviewOrderIds),
            $orders,
        );
    }

    public static function detail(Order $order, bool $reviewExists = false): array
    {
        return [
            ...self::listItem($order, $reviewExists ? [$order->id()] : []),
            'needs_delivery' => $order->needsDelivery(),
            'delivery_address' => $order->deliveryAddress(),
        ];
    }
}
