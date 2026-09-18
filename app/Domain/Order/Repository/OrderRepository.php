<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Aggregate\Order;

interface OrderRepository
{
    public function save(Order $order): Order;

    public function findById(int $id): ?Order;

    /**
     * @param  list<string>|null  $statusesIn
     * @param  list<string>|null  $statusesNotIn
     * @return list<Order>
     */
    public function all(
        ?int $clientId = null,
        ?string $status = null,
        ?int $masterId = null,
        ?int $equipmentId = null,
        ?array $statusesIn = null,
        ?array $statusesNotIn = null,
    ): array;

    /**
     * @param  list<string>  $statuses
     * @return array<string, int>
     */
    public function countByStatuses(array $statuses): array;
}
