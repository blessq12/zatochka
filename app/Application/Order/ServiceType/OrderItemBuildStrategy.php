<?php

namespace App\Application\Order\ServiceType;

use App\Application\Order\DTO\CreateOrderItemDTO;
use App\Domain\Order\Entity\OrderItem;

interface OrderItemBuildStrategy
{
    public function buildItem(CreateOrderItemDTO $itemDto): OrderItem;
}
