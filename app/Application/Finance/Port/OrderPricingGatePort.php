<?php

namespace App\Application\Finance\Port;

/**
 * Cross-BC read gate for pricing writes.
 * Pricing write-path gate: Order Application must not depend on Order write Repository.
 */
interface OrderPricingGatePort
{
    public function assertAwaitingPricing(string $orderId): void;

    public function assertItemPricable(string $orderId, int $orderItemId): void;
}
