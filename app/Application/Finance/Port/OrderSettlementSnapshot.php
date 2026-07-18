<?php

namespace App\Application\Finance\Port;

/**
 * ACL snapshot заказа для settlement при выдаче / gate ручного AcceptPayment.
 * Без гидрации Order-агрегата.
 */
final readonly class OrderSettlementSnapshot
{
    public function __construct(
        public string $orderId,
        public string $orderNumber,
        public string $status,
        public string $billingType,
        public ?string $totalAmount,
        public string $currency,
    ) {}

    public function isWarranty(): bool
    {
        return $this->billingType === 'warranty';
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function hasChargeableTotal(): bool
    {
        return $this->totalAmount !== null && (float) $this->totalAmount > 0;
    }
}
