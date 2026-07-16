<?php


interface CompletedOrderPort
{
    public function isCompletedForClient(string $orderId, int $clientId): bool;
}
