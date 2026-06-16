<?php

namespace App\Domain\OrderFulfillment\Repositories;

use App\Domain\OrderFulfillment\Entities\Order;

interface OrderRepositoryInterface
{
    public function findById(int $id): ?Order;

    public function save(Order $order): Order;

    public function findLastOrderNumberForYear(int $year): ?string;
}
