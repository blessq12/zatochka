<?php

namespace App\Application\Finance\Query;

use App\Application\Finance\DTO\PaymentDTO;
use App\Application\Finance\ReadPort\PaymentReadPort;

final readonly class GetPaymentByIdHandler
{
    public function __construct(
        private PaymentReadPort $readPort,
    ) {}

    public function handle(GetPaymentByIdQuery $query): ?PaymentDTO
    {
        return $this->readPort->findById($query->paymentId);
    }
}
