<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;

interface OrderRepository
{
    // base methods
    public function get(int $id): Order;
    public function create(array $data): Order;
    public function update(Order $order, array $data): Order;
    public function delete(Order $order): void;

    // custom methods
    public function checkExists(int $id): bool;
    public function existsByNumber(string $orderNumber): bool;
}
