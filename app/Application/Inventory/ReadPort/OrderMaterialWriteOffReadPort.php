<?php

namespace App\Application\Inventory\ReadPort;

use App\Application\Inventory\DTO\OrderMaterialWriteOffLineDTO;

interface OrderMaterialWriteOffReadPort
{
    /**
     * @return list<OrderMaterialWriteOffLineDTO>
     */
    public function listActiveByOrderId(string $orderId): array;
}
