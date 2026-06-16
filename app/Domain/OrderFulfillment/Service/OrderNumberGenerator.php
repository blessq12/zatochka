<?php

namespace App\Domain\OrderFulfillment\Service;

use App\Domain\OrderFulfillment\Repository\OrderRepositoryInterface;
use App\Domain\OrderFulfillment\ValueObject\OrderNumber;

final class OrderNumberGenerator
{
    public function __construct(
        private OrderRepositoryInterface $orders,
    ) {}

    public function generate(): OrderNumber
    {
        $year = (int) date('Y');
        $lastNumber = $this->orders->findLastOrderNumberForYear($year);
        $sequence = 1;

        if ($lastNumber !== null && preg_match('/ORD-\d{4}-(\d+)/', $lastNumber, $matches)) {
            $sequence = (int) $matches[1] + 1;
        }

        return new OrderNumber(sprintf(
            'ORD-%s-%04d',
            $year,
            $sequence,
        ));
    }
}
