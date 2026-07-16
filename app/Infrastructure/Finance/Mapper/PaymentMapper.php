<?php

namespace App\Infrastructure\Finance\Mapper;

use App\Application\Finance\DTO\PaymentDTO;
use App\Domain\Finance\Entity\Payment;
use App\Domain\Finance\Entity\Refund;
use App\Domain\Finance\VO\PaymentMethod;
use App\Infrastructure\Finance\Model\PaymentModel;
use App\Infrastructure\Finance\Model\RefundModel;
use App\Domain\Order\VO\OrderId;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final class PaymentMapper
{
    public function toDomain(PaymentModel $model): Payment
    {
        $refunds = [];

        foreach ($model->refunds as $row) {
            $refunds[] = new Refund(
                new EntityId((int) $row->id),
                new EntityId((int) $row->payment_id),
                new Money((string) $row->amount, (string) $row->currency),
                $row->reason !== null ? (string) $row->reason : null,
                DateTimeImmutable::createFromInterface($row->created_at),
            );
        }

        return Payment::reconstitute(
            new EntityId((int) $model->id),
            new OrderId((string) $model->order_id),
            new Money((string) $model->amount, (string) $model->currency),
            PaymentMethod::from((string) $model->method),
            DateTimeImmutable::createFromInterface($model->accepted_at),
            $refunds,
        );
    }

    public function toPersistence(Payment $payment, ?PaymentModel $model = null): PaymentModel
    {
        $model ??= new PaymentModel();
        $model->id = $payment->id()->value;
        $model->order_id = $payment->orderId()->value;
        $model->amount = $payment->amount()->amount;
        $model->currency = $payment->amount()->currency;
        $model->method = $payment->method()->value;
        $model->accepted_at = $payment->acceptedAt();
        $model->created_at = $model->created_at ?? now();
        $model->updated_at = now();

        return $model;
    }

    /** @return list<RefundModel> */
    public function refundsToPersistence(Payment $payment): array
    {
        $rows = [];

        foreach ($payment->refunds() as $refund) {
            $row = new RefundModel();
            $row->id = $refund->id()->value;
            $row->payment_id = $payment->id()->value;
            $row->amount = $refund->amount()->amount;
            $row->currency = $refund->amount()->currency;
            $row->reason = $refund->reason();
            $row->created_at = $refund->createdAt();
            $rows[] = $row;
        }

        return $rows;
    }

    public function toDTO(PaymentModel $model): PaymentDTO
    {
        return new PaymentDTO(
            (int) $model->id,
            (string) $model->order_id,
            (string) $model->amount,
            (string) $model->currency,
            (string) $model->method,
            $model->accepted_at->toIso8601String(),
        );
    }
}
