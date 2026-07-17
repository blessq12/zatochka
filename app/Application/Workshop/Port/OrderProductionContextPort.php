<?php

namespace App\Application\Workshop\Port;

use App\Domain\Order\Entity\Order;
use App\Domain\Order\VO\OrderId;

/**
 * Cross-BC read of Order for production policies / work attachment.
 * Workshop Application must not depend on Order write Repository.
 */
interface OrderProductionContextPort
{
    public function getById(OrderId $orderId): Order;
}
