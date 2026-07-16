<?php

namespace App\Infrastructure\Order\Mapper;

use App\Application\Order\DTO\OrderDTO;
use App\Application\Order\DTO\OrderItemDTO;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\Entity\ReceptionData;
use App\Domain\Order\VO\OrderBillingType;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderId;
use App\Domain\Order\VO\OrderNumber;
use App\Domain\Order\VO\OrderServiceType;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\OrderUrgency;
use App\Domain\Order\VO\ReceptionCondition;
use App\Domain\Order\VO\SharpeningToolType;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Order\Model\ReceptionDataModel;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class OrderMapper
{
    public function toDomain(OrderModel $model): Order
    {
        $items = [];

        foreach ($model->items as $itemModel) {
            $reception = null;

            if ($itemModel->reception !== null) {
                $reception = new ReceptionData(
                    new EntityId((int) $itemModel->reception->id),
                    new ReceptionCondition(
                        (string) $itemModel->reception->condition_description,
                        $itemModel->reception->visual_notes !== null
                            ? (string) $itemModel->reception->visual_notes
                            : null,
                    ),
                    DateTimeImmutable::createFromInterface($itemModel->reception->received_at),
                    $itemModel->reception->attachment_refs ?? [],
                );
            }

            $items[] = OrderItem::reconstitute(
                new EntityId((int) $itemModel->id),
                $itemModel->client_equipment_id !== null
                    ? new EntityId((int) $itemModel->client_equipment_id)
                    : null,
                $itemModel->tool_name !== null ? (string) $itemModel->tool_name : null,
                OrderItemStatus::from((string) $itemModel->status),
                $reception,
                $itemModel->production_task_id !== null
                    ? new EntityId((int) $itemModel->production_task_id)
                    : null,
                $itemModel->item_price_id !== null
                    ? new EntityId((int) $itemModel->item_price_id)
                    : null,
                $itemModel->warranty_id !== null
                    ? new EntityId((int) $itemModel->warranty_id)
                    : null,
                $itemModel->tool_type !== null
                    ? SharpeningToolType::from((string) $itemModel->tool_type)
                    : null,
                $itemModel->quantity !== null ? (int) $itemModel->quantity : null,
            );
        }

        return Order::reconstitute(
            new OrderId((string) $model->id),
            new OrderNumber((string) $model->number),
            new EntityId((int) $model->client_id),
            new Money((string) $model->estimated_amount, (string) $model->estimated_currency),
            DateTimeImmutable::createFromInterface($model->created_at),
            OrderStatus::from((string) $model->status),
            $items,
            OrderServiceType::from((string) $model->service_type),
            OrderBillingType::from((string) $model->billing_type),
            OrderUrgency::from((string) $model->urgency),
            (bool) $model->delivery_required,
            $model->defects !== null ? (string) $model->defects : null,
            $model->internal_notes !== null ? (string) $model->internal_notes : null,
            $model->warranty_source_order_id !== null
                ? new OrderId((string) $model->warranty_source_order_id)
                : null,
        );
    }

    public function toPersistence(Order $order, ?OrderModel $model = null): OrderModel
    {
        $model ??= new OrderModel();
        $model->id = $order->id()->value;
        $model->number = $order->number()->value;
        $model->client_id = $order->clientId()->value;
        $model->status = $order->status()->value;
        $model->service_type = $order->serviceType()->value;
        $model->billing_type = $order->billingType()->value;
        $model->urgency = $order->urgency()->value;
        $model->delivery_required = $order->deliveryRequired();
        $model->defects = $order->defects();
        $model->internal_notes = $order->internalNotes();
        $model->warranty_source_order_id = $order->warrantySourceOrderId()?->value;
        $model->estimated_amount = $order->estimatedCost()->amount;
        $model->estimated_currency = $order->estimatedCost()->currency;
        $model->created_at = $order->createdAt();
        $model->updated_at = now();

        return $model;
    }

    /** @return list<OrderItemModel> */
    public function itemsToPersistence(Order $order): array
    {
        $rows = [];

        foreach ($order->items() as $item) {
            $row = new OrderItemModel();
            $row->id = $item->id()->value;
            $row->order_id = $order->id()->value;
            $row->client_equipment_id = $item->clientEquipmentId()?->value;
            $row->tool_name = $item->toolName();
            $row->tool_type = $item->toolType()?->value;
            $row->quantity = $item->quantity();
            $row->status = $item->status()->value;
            $row->production_task_id = $item->productionTaskId()?->value;
            $row->item_price_id = $item->itemPriceId()?->value;
            $row->warranty_id = $item->warrantyId()?->value;
            $rows[] = $row;
        }

        return $rows;
    }

    /** @return list<ReceptionDataModel> */
    public function receptionToPersistence(Order $order): array
    {
        $rows = [];

        foreach ($order->items() as $item) {
            $reception = $item->receptionData();

            if ($reception === null) {
                continue;
            }

            $row = new ReceptionDataModel();
            $row->id = $reception->id()->value;
            $row->order_item_id = $item->id()->value;
            $row->condition_description = $reception->condition()->description;
            $row->visual_notes = $reception->condition()->visualNotes;
            $row->attachment_refs = $reception->attachmentRefs();
            $row->received_at = $reception->receivedAt();
            $rows[] = $row;
        }

        return $rows;
    }

    public function toDTO(OrderModel $model): OrderDTO
    {
        $items = [];

        foreach ($model->items as $item) {
            $items[] = new OrderItemDTO(
                (int) $item->id,
                $item->client_equipment_id !== null ? (int) $item->client_equipment_id : null,
                $item->tool_name !== null ? (string) $item->tool_name : null,
                $item->tool_type !== null ? (string) $item->tool_type : null,
                $item->quantity !== null ? (int) $item->quantity : null,
                (string) $item->status,
                $item->reception !== null,
                $item->production_task_id !== null ? (int) $item->production_task_id : null,
                $item->item_price_id !== null ? (int) $item->item_price_id : null,
                $item->warranty_id !== null ? (int) $item->warranty_id : null,
            );
        }

        return new OrderDTO(
            (string) $model->id,
            (string) $model->number,
            (int) $model->client_id,
            (string) $model->status,
            (string) $model->service_type,
            (string) $model->billing_type,
            (string) $model->urgency,
            (bool) $model->delivery_required,
            $model->defects !== null ? (string) $model->defects : null,
            $model->internal_notes !== null ? (string) $model->internal_notes : null,
            $model->warranty_source_order_id !== null ? (string) $model->warranty_source_order_id : null,
            (string) $model->estimated_amount,
            (string) $model->estimated_currency,
            $model->created_at->toIso8601String(),
            $items,
        );
    }
}
