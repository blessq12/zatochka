<?php

namespace App\Application\Documents\Port;

use App\Application\Documents\DTO\OrderDocumentSnapshot;

interface OrderDocumentReadPort
{
    public function findById(string $orderId): ?OrderDocumentSnapshot;
}
