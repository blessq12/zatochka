<?php

namespace App\Infrastructure\Order\Repository;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Repository\OrderRepository;
use App\Infrastructure\Order\Mapper\OrderMapper;
use App\Infrastructure\Order\Model\OrderItemModel;
use App\Infrastructure\Order\Model\OrderModel;
use App\Infrastructure\Order\Model\ReceptionDataModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
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

            ReceptionDataModel::query()
                ->whereIn('order_item_id', OrderItemModel::query()->where('order_id', $order->id()->value)->pluck('id'))
                ->delete();

            OrderItemModel::query()->where('order_id', $order->id()->value)->delete();

            foreach ($this->mapper->itemsToPersistence($order) as $row) {
                $row->save();
            }

            foreach ($this->mapper->receptionToPersistence($order) as $row) {
                $row->save();
            }
        });
    }

    public function findById(EntityId $id): ?Order
    {
        $model = OrderModel::query()->with(['items.reception'])->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): Order
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Order %d not found.', $id->value));
    }
}
