<?php

namespace App\Domain\OrderFulfillment\Repository;

use App\Domain\OrderFulfillment\Entity\Order;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function save(Order $order): Order;

    public function findLastOrderNumberForYear(int $year): ?string;
}
