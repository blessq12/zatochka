<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Order\VO\OrderId;
use App\Infrastructure\Order\Mapper\OrderMapper;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Order\Model\ReceptionDataModel;
use App\Shared\Domain\DomainException;
use Illuminate\Support\Facades\DB;

final readonly class EloquentOrderRepository implements OrderRepository
{
    public function __construct(
        private OrderMapper $mapper,
    ) {}

    public function save(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $model = OrderModel::query()->find($order->id()->value);
            $model = $this->mapper->toPersistence($order, $model);
            $model->save();

            $keepItemIds = [];

            foreach ($this->mapper->itemsToPersistence($order) as $row) {
                $keepItemIds[] = (int) $row->id;

                OrderItemModel::query()->updateOrCreate(
                    ['id' => $row->id],
                    [
                        'order_id' => $row->order_id,
                        'client_equipment_id' => $row->client_equipment_id,
                        'tool_name' => $row->tool_name,
                        'tool_type' => $row->tool_type,
                        'quantity' => $row->quantity,
                        'rejected_quantity' => $row->rejected_quantity,
                        'rejection_reason' => $row->rejection_reason,
                        'status' => $row->status,
                        'item_price_id' => $row->item_price_id,
                        'warranty_id' => $row->warranty_id,
                    ],
                );
            }

            $orphanItemIds = OrderItemModel::query()
                ->where('order_id', $order->id()->value)
                ->when(
                    $keepItemIds !== [],
                    static fn ($query) => $query->whereNotIn('id', $keepItemIds),
                    static fn ($query) => $query,
                )
                ->pluck('id')
                ->all();

            if ($orphanItemIds !== []) {
                ReceptionDataModel::query()->whereIn('order_item_id', $orphanItemIds)->delete();
                OrderItemModel::query()->whereIn('id', $orphanItemIds)->delete();
            }

            $keepReceptionIds = [];

            foreach ($this->mapper->receptionToPersistence($order) as $row) {
                $keepReceptionIds[] = (int) $row->id;

                ReceptionDataModel::query()->updateOrCreate(
                    ['id' => $row->id],
                    [
                        'order_item_id' => $row->order_item_id,
                        'condition_description' => $row->condition_description,
                        'visual_notes' => $row->visual_notes,
                        'attachment_refs' => $row->attachment_refs,
                        'received_at' => $row->received_at,
                    ],
                );
            }

            $receptionQuery = ReceptionDataModel::query()
                ->whereIn('order_item_id', $keepItemIds);

            if ($keepReceptionIds !== []) {
                $receptionQuery->whereNotIn('id', $keepReceptionIds);
            }

            if ($keepItemIds !== []) {
                $receptionQuery->delete();
            }
        });
    }

    public function findById(OrderId $id): ?Order
    {
        $model = OrderModel::query()->with(['items.reception'])->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(OrderId $id): Order
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Order %s not found.', $id->value));
    }
}
