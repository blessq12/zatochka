<?php

namespace App\Infrastructure\OrderFulfillment\Persistence\Mapper;

use App\Domain\OrderFulfillment\Entity\Order;
use App\Domain\OrderFulfillment\Entity\OrderMaterial;
use App\Domain\OrderFulfillment\Entity\OrderTool;
use App\Domain\OrderFulfillment\Entity\OrderWork;
use App\Domain\OrderFulfillment\ValueObject\ClientSnapshot;
use App\Domain\OrderFulfillment\ValueObject\OrderNumber;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderMaterialModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderToolModel;
use App\Infrastructure\OrderFulfillment\Persistence\Eloquent\OrderWorkModel;
use DateTimeImmutable;

final class OrderMapper
{
    public function toDomain(OrderModel $model): Order
    {
        return new Order(
            id: $model->id,
            orderNumber: new OrderNumber($model->order_number),
            status: $model->status,
            serviceTypes: $model->service_types ?? [],
            urgency: $model->urgency,
            isWarranty: $model->is_warranty,
            needsDelivery: $model->needs_delivery,
            deliveryAddress: $model->delivery_address,
            problemDescription: $model->problem_description,
            internalNotes: $model->internal_notes,
            reworkFeedback: $model->rework_feedback,
            reworkReturnedAt: $this->toImmutable($model->rework_returned_at),
            reworkReturnedBy: $model->rework_returned_by,
            price: $model->price !== null ? (string) $model->price : null,
            source: $model->source,
            clientSnapshot: ClientSnapshot::fromArray($model->client_snapshot),
            leadId: $model->lead_id,
            clientId: $model->client_id,
            equipmentId: $model->equipment_id,
            masterId: $model->master_id,
            managerId: $model->manager_id,
            branchId: $model->branch_id,
            warrantyParentOrderId: $model->warranty_parent_order_id,
            takenAt: $this->toImmutable($model->taken_at),
            readyAt: $this->toImmutable($model->ready_at),
            issuedAt: $this->toImmutable($model->issued_at),
            createdAt: $this->toImmutable($model->created_at),
            works: $model->relationLoaded('works')
                ? $model->works->map(fn (OrderWorkModel $work) => new OrderWork(
                    id: $work->id,
                    description: $work->description,
                    price: $work->price !== null ? (string) $work->price : null,
                    sortOrder: $work->sort_order,
                    toolType: $work->tool_type,
                ))->all()
                : [],
            tools: $model->relationLoaded('tools')
                ? $model->tools->map(fn (OrderToolModel $tool) => new OrderTool(
                    id: $tool->id,
                    toolType: $tool->tool_type,
                    quantity: $tool->quantity,
                    name: $tool->name,
                    unitPrice: $tool->unit_price !== null ? (string) $tool->unit_price : null,
                ))->all()
                : [],
            materials: $model->relationLoaded('materials')
                ? $model->materials->map(fn (OrderMaterialModel $material) => new OrderMaterial(
                    id: $material->id,
                    warehouseItemId: $material->warehouse_item_id,
                    quantity: (string) $material->quantity,
                    unitPrice: (string) $material->unit_price,
                    totalPrice: (string) $material->total_price,
                ))->all()
                : [],
        );
    }

    public function fillModel(Order $order, OrderModel $model): void
    {
        $model->fill([
            'order_number' => $order->orderNumber()->value,
            'status' => $order->status(),
            'service_types' => $order->serviceTypes(),
            'urgency' => $order->urgency(),
            'is_warranty' => $order->isWarranty(),
            'needs_delivery' => $order->needsDelivery(),
            'delivery_address' => $order->deliveryAddress(),
            'problem_description' => $order->problemDescription(),
            'internal_notes' => $order->internalNotes(),
            'rework_feedback' => $order->reworkFeedback(),
            'rework_returned_at' => $order->reworkReturnedAt(),
            'rework_returned_by' => $order->reworkReturnedBy(),
            'price' => $order->price(),
            'source' => $order->source(),
            'client_snapshot' => $order->clientSnapshot()?->toArray(),
            'lead_id' => $order->leadId(),
            'client_id' => $order->clientId(),
            'equipment_id' => $order->equipmentId(),
            'master_id' => $order->masterId(),
            'manager_id' => $order->managerId(),
            'branch_id' => $order->branchId(),
            'warranty_parent_order_id' => $order->warrantyParentOrderId(),
            'taken_at' => $order->takenAt(),
            'ready_at' => $order->readyAt(),
            'issued_at' => $order->issuedAt(),
        ]);
    }

    private function toImmutable(mixed $value): ?DateTimeImmutable
    {
        if ($value === null) {
            return null;
        }

        return DateTimeImmutable::createFromInterface($value) ?: null;
    }
}
