<?php

namespace App\Application\Workshop\Port;

use App\Application\Workshop\DTO\OrderProductionContextDTO;

/**
 * Workshop-owned read projection of Order for production completion / work attachment.
 */
interface OrderProductionContextPort
{
    public function getById(string $orderId): OrderProductionContextDTO;
}
