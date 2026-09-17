<?php

namespace App\Domain\Finance\Repository;

use App\Domain\Finance\Aggregate\OrderPricing;

interface OrderPricingRepository
{
    public function save(OrderPricing $pricing): OrderPricing;

    public function findByOrderId(int $orderId): ?OrderPricing;
}
