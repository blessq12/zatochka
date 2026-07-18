<?php

namespace App\Application\Feedback\Port;

interface CompletedOrderPort
{
    public function isCompletedForClient(string $orderId, int $clientId): bool;
}
