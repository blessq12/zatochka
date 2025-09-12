<?php

namespace App\Infrastructure\Repository\Order;

use App\Domain\Order\Repository\OrderRepository;
use App\Models\Order;

class OrderRepositoryImpl implements OrderRepository
{
    public function get(int $id): Order
    {
        return Order::find($id);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order;
    }

    public function delete(Order $order): void
    {
        $order->delete();
    }

    public function checkExists(int $id): bool
    {
        return Order::where('id', $id)->exists();
    }

    public function existsByNumber(string $orderNumber): bool
    {
        return Order::where('order_number', $orderNumber)->exists();
    }
}
