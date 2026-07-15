<?php

namespace App\Domain\Order\Repository;

use App\Domain\Order\Entity\Order;
use App\Shared\ValueObject\EntityId;

interface OrderRepository
{
    public function save(Order $order): void;

    public function findById(EntityId $id): ?Order;

    public function getById(EntityId $id): Order;
}
