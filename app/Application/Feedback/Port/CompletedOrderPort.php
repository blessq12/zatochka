<?php

namespace App\Application\Feedback\Port;

interface CompletedOrderPort
{
    public function isCompletedForClient(int $orderId, int $clientId): bool;
}
