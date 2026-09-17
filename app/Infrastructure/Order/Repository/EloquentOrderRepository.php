<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Aggregate\Order;
use App\Domain\Order\BillingType;
use App\Domain\Order\Entity\OrderItem;
use App\Domain\Order\OrderItemKind;
use App\Domain\Order\OrderStatus;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\Urgency;
use App\Infrastructure\Order\Eloquent\OrderItemModel;
use App\Infrastructure\Order\Eloquent\OrderModel;
use Illuminate\Support\Facades\DB;

final class EloquentOrderRepository implements OrderRepository
{
    public function save(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            /** @var OrderModel $model */
            $model = $order->id() === null
                ? new OrderModel()
                : OrderModel::query()->findOrFail($order->id());

            $model->client_id = $order->clientId();
            $model->master_id = $order->masterId();
            $model->billing_type = $order->billingType()->value;
            $model->urgency = $order->urgency()->value;
            $model->estimated_cost = $order->estimatedCost();
            $model->needs_delivery = $order->needsDelivery();
            $model->delivery_address = $order->deliveryAddress();
            $model->status = $order->status()->value;
            $model->save();

            if ($order->id() === null) {
                $order->assignId((int) $model->id);
            }

            if ($this->shouldSyncItems($order)) {
                OrderItemModel::query()->where('order_id', $model->id)->delete();

                foreach ($order->items() as $index => $item) {
                    $itemModel = new OrderItemModel([
                        'order_id' => $model->id,
                        'kind' => $item->kind()->value,
                        'title' => $item->title(),
                        'quantity' => $item->quantity(),
                        'comment' => null,
                        'equipment_id' => $item->equipmentId(),
                        'problem' => $item->problem(),
                        'position' => $item->position() ?: $index,
                    ]);
                    $itemModel->save();
                    $item->assignId((int) $itemModel->id);
                }
            }

            return $order;
        });
    }

    public function findById(int $id): ?Order
    {
        /** @var OrderModel|null $model */
        $model = OrderModel::query()->with('items')->find($id);

        return $model === null ? null : $this->toDomain($model);
    }

    public function all(
        ?int $clientId = null,
        ?string $status = null,
        ?int $masterId = null,
        ?int $equipmentId = null,
    ): array {
        $query = OrderModel::query()->with('items')->orderByDesc('id');

        if ($clientId !== null) {
            $query->where('client_id', $clientId);
        }
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }
        if ($masterId !== null) {
            $query->where('master_id', $masterId);
        }
        if ($equipmentId !== null) {
            $query->whereHas('items', static function ($items) use ($equipmentId): void {
                $items->where('equipment_id', $equipmentId);
            });
        }

        return $query
            ->get()
            ->map(fn (OrderModel $model): Order => $this->toDomain($model))
            ->values()
            ->all();
    }

    private function shouldSyncItems(Order $order): bool
    {
        foreach ($order->items() as $item) {
            if ($item->id() === null) {
                return true;
            }
        }

        return false;
    }

    private function toDomain(OrderModel $model): Order
    {
        $items = $model->items
            ->map(static function (OrderItemModel $item): OrderItem {
                $kind = OrderItemKind::from((string) $item->kind);

                return new OrderItem(
                    (int) $item->id,
                    $kind,
                    $item->title,
                    $item->quantity !== null ? (int) $item->quantity : null,
                    $item->equipment_id !== null ? (int) $item->equipment_id : null,
                    $item->problem,
                    (int) $item->position,
                );
            })
            ->values()
            ->all();

        return new Order(
            (int) $model->id,
            (int) $model->client_id,
            BillingType::from((string) $model->billing_type),
            Urgency::from((string) $model->urgency),
            OrderStatus::from((string) $model->status),
            $model->master_id !== null ? (int) $model->master_id : null,
            (string) ($model->estimated_cost ?? '0'),
            (bool) $model->needs_delivery,
            $model->delivery_address,
            $items,
        );
    }
}
