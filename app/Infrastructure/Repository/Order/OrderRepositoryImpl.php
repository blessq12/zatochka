<?php

namespace App\Infrastructure\Repository\Order;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\Mapper\OrderMapper;
use App\Domain\Order\Repository\OrderRepository;
use App\Models\Order as EloquentOrder;

class OrderRepositoryImpl implements OrderRepository
{
    public function __construct(
        private OrderMapper $mapper
    ) {}

    public function get(int $id): Order
    {
        $eloquentOrder = EloquentOrder::findOrFail($id);
        return $this->mapper->toDomain($eloquentOrder);
    }

    public function create(array $data): Order
    {
        $eloquentOrder = EloquentOrder::create($data);
        return $this->mapper->toDomain($eloquentOrder);
    }

    public function update(Order $order, array $data): Order
    {
        $eloquentOrder = EloquentOrder::findOrFail($order->getId());
        $eloquentOrder->update($data);

        return $this->mapper->toDomain($eloquentOrder);
    }

    public function delete(Order $order): void
    {
        $eloquentOrder = EloquentOrder::findOrFail($order->getId());
        $eloquentOrder->delete();
    }

    public function checkExists(int $id): bool
    {
        return EloquentOrder::where('id', $id)->exists();
    }

    public function existsByNumber(string $orderNumber): bool
    {
        return EloquentOrder::where('order_number', $orderNumber)->exists();
    }
}
