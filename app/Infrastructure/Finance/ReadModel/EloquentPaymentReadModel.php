<?php

namespace App\Infrastructure\Finance\ReadModel;

use App\Application\Finance\DTO\PaymentDTO;
use App\Application\Finance\ReadPort\PaymentReadPort;
use App\Infrastructure\Finance\Mapper\PaymentMapper;
use App\Infrastructure\Finance\Model\PaymentModel;

final readonly class EloquentPaymentReadModel implements PaymentReadPort
{
    public function __construct(
        private PaymentMapper $mapper,
    ) {}

    public function findById(int $paymentId): ?PaymentDTO
    {
        $model = PaymentModel::query()->find($paymentId);

        return $model === null ? null : $this->mapper->toDTO($model);
    }
}
