<?php

namespace App\Application\Order\ReadPort;

use App\Application\Order\DTO\OrderDTO;

interface OrderReadPort
{
    public function findById(int $orderId): ?OrderDTO;

    /** @return list<OrderDTO> */
    public function listByClientId(int $clientId): array;
}
