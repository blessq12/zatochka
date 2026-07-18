<?php

namespace App\Application\Finance\Port;

interface OrderSettlementPort
{
    public function snapshot(string $orderId): OrderSettlementSnapshot;
}
