<?php

namespace App\Domain\Order\Repository;

use App\Models\Order;

interface OrderRepository
{
    public function get(int $id): Order;
    public function create(array $data): Order;
    public function update(Order $order, array $data): Order;
    public function delete(Order $order): void;
}
