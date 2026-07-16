<?php

namespace App\Application\Order\ReadPort;

use App\Application\Order\DTO\OrderContainerDTO;

interface OrderContainerReadPort
{
    public function findById(string $orderId): ?OrderContainerDTO;
}
