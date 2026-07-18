<?php

namespace App\Application\Finance\ReadPort;

use App\Application\Finance\DTO\PaymentDTO;

interface PaymentReadPort
{
    public function findById(int $paymentId): ?PaymentDTO;

    public function findByOrderId(string $orderId): ?PaymentDTO;
}
