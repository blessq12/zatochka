<?php

namespace App\Infrastructure\Order\Mapper;

use App\Application\Order\DTO\OrderDTO;
use App\Application\Order\DTO\OrderItemDTO;
use App\Domain\Order\Entity\Order;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\Entity\ReceptionData;
use App\Domain\Order\VO\OrderItemStatus;
use App\Domain\Order\VO\OrderStatus;
use App\Domain\Order\VO\ReceptionCondition;
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
                new EntityId((int) $itemModel->client_equipment_id),
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
            );
        }

        return Order::reconstitute(
            new EntityId((int) $model->id),
            new EntityId((int) $model->client_id),
            new Money((string) $model->estimated_amount, (string) $model->estimated_currency),
            DateTimeImmutable::createFromInterface($model->created_at),
            OrderStatus::from((string) $model->status),
            $items,
        );
    }

    public function toPersistence(Order $order, ?OrderModel $model = null): OrderModel
    {
        $model ??= new OrderModel();
        $model->id = $order->id()->value;
        $model->client_id = $order->clientId()->value;
        $model->status = $order->status()->value;
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
            $row->client_equipment_id = $item->clientEquipmentId()->value;
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
                (int) $item->client_equipment_id,
                (string) $item->status,
                $item->reception !== null,
                $item->production_task_id !== null ? (int) $item->production_task_id : null,
                $item->item_price_id !== null ? (int) $item->item_price_id : null,
                $item->warranty_id !== null ? (int) $item->warranty_id : null,
            );
        }

        return new OrderDTO(
            (int) $model->id,
            (int) $model->client_id,
            (string) $model->status,
            (string) $model->estimated_amount,
            (string) $model->estimated_currency,
            $model->created_at->toIso8601String(),
            $items,
        );
    }
}
