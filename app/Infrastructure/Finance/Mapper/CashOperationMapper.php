<?php

namespace App\Infrastructure\Finance\Mapper;

use App\Domain\Finance\Entity\CashOperation;
use App\Domain\Finance\VO\CashOperationType;
use App\Domain\Finance\VO\PaymentMethod;
use App\Infrastructure\Finance\Model\CashOperationModel;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class CashOperationMapper
{
    public function toDomain(CashOperationModel $model): CashOperation
    {
        return CashOperation::reconstitute(
            new EntityId((int) $model->id),
            CashOperationType::from((string) $model->type),
            new Money((string) $model->amount, (string) $model->currency),
            DateTimeImmutable::createFromInterface($model->registered_at),
            $model->comment !== null ? (string) $model->comment : null,
            $model->payment_id !== null ? new EntityId((int) $model->payment_id) : null,
            $model->refund_id !== null ? new EntityId((int) $model->refund_id) : null,
            $model->payment_method !== null ? PaymentMethod::from((string) $model->payment_method) : null,
        );
    }

    public function toPersistence(CashOperation $operation, ?CashOperationModel $model = null): CashOperationModel
    {
        $model ??= new CashOperationModel;
        $model->id = $operation->id()->value;
        $model->type = $operation->type()->value;
        $model->payment_method = $operation->paymentMethod()?->value;
        $model->amount = $operation->amount()->amount;
        $model->currency = $operation->amount()->currency;
        $model->comment = $operation->comment();
        $model->payment_id = $operation->paymentId()?->value;
        $model->refund_id = $operation->refundId()?->value;
        $model->registered_at = $operation->registeredAt();
        $model->created_at = $model->created_at ?? now();
        $model->updated_at = now();

        return $model;
    }
}
