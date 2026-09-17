<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Aggregate\Order;

interface OrderRepository
{
    public function save(Order $order): Order;

    public function findById(int $id): ?Order;

    /**
     * @return list<Order>
     */
    public function all(
        ?int $clientId = null,
        ?string $status = null,
        ?int $masterId = null,
        ?int $equipmentId = null,
    ): array;
}
