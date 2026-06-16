<?php

namespace App\Application\OrderFulfillment\Presenter;

use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderMaterial;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Entity\OrderWork;
use DateTimeInterface;

final class PosOrderPresenter
{
    /** @return array<string, mixed> */
    public static function listItem(Order $order): array
    {
        return [
            'id' => $order->id(),
            'order_number' => $order->orderNumber()->value,
            'status' => $order->status()->value,
            'status_label' => $order->status()->label(),
            'urgency' => $order->urgency()?->value,
            'service_types' => $order->serviceTypes(),
            'price' => $order->price(),
            'client_name' => $order->clientDisplayName(),
            'client_phone' => $order->clientDisplayPhone(),
            'needs_delivery' => $order->needsDelivery(),
            'created_at' => $order->createdAt()?->format(DateTimeInterface::ATOM),
        ];
    }

    /** @return array<string, mixed> */
    public static function detail(Order $order): array
    {
        return [
            ...self::listItem($order),
            'is_warranty' => $order->isWarranty(),
            'warranty_parent_order_id' => $order->warrantyParentOrderId(),
            'delivery_address' => $order->deliveryAddress(),
            'problem_description' => $order->problemDescription(),
            'internal_notes' => $order->internalNotes(),
            'tools' => array_map(
                static fn (OrderTool $tool): array => [
                    'id' => $tool->id,
                    'tool_type' => $tool->toolType,
                    'quantity' => $tool->quantity,
                ],
                $order->tools(),
            ),
            'works' => array_map(
                static fn (OrderWork $work): array => [
                    'id' => $work->id,
                    'description' => $work->description,
                    'price' => $work->price,
                    'sort_order' => $work->sortOrder,
                ],
                $order->works(),
            ),
            'materials' => array_map(
                static fn (OrderMaterial $material): array => [
                    'id' => $material->id,
                    'warehouse_item_id' => $material->warehouseItemId,
                    'quantity' => $material->quantity,
                    'unit_price' => $material->unitPrice,
                    'total_price' => $material->totalPrice,
                ],
                $order->materials(),
            ),
            'taken_at' => $order->takenAt()?->format(DateTimeInterface::ATOM),
            'ready_at' => $order->readyAt()?->format(DateTimeInterface::ATOM),
        ];
    }

    /** @param list<Order> $orders */
    public static function list(array $orders): array
    {
        return array_map(self::listItem(...), $orders);
    }
}
