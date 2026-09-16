<?php

namespace App\Application\Order\Port;

use App\Application\Order\DTO\OrderDocumentSnapshot;

interface OrderDocumentReadPort
{
    public function findById(string $orderId): ?OrderDocumentSnapshot;
}
