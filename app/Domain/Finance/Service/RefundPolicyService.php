<?php

namespace App\Domain\Finance\Service;

use App\Domain\Finance\Entity\Payment;
use App\Domain\Finance\Entity\Refund;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final class RefundPolicyService
{
    public function refund(
        Payment $payment,
        EntityId $refundId,
        Money $amount,
        string $orderNumber,
        ?string $reason = null,
    ): Refund {
        return $payment->createRefund($refundId, $amount, $orderNumber, $reason);
    }
}
