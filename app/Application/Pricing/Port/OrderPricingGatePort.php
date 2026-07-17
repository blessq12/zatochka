<?php

namespace App\Application\Pricing\Port;

/**
 * Cross-BC read gate for pricing writes.
 * Pricing Application must not depend on Order write Repository.
 */
interface OrderPricingGatePort
{
    public function assertAwaitingPricing(string $orderId): void;

    public function assertItemPricable(string $orderId, int $orderItemId): void;
}
