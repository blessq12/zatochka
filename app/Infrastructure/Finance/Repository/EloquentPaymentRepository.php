<?php

namespace App\Infrastructure\Finance\Repository;

use App\Domain\Finance\Entity\Payment;
use App\Domain\Finance\Repository\PaymentRepository;
use App\Domain\Order\VO\OrderId;
use App\Infrastructure\Finance\Mapper\PaymentMapper;
use App\Infrastructure\Finance\Model\PaymentModel;
use App\Infrastructure\Finance\Model\RefundModel;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;
use Illuminate\Support\Facades\DB;

final readonly class EloquentPaymentRepository implements PaymentRepository
{
    public function __construct(
        private PaymentMapper $mapper,
    ) {}

    public function save(Payment $payment): void
    {
        DB::transaction(function () use ($payment): void {
            $model = PaymentModel::query()->find($payment->id()->value);
            $model = $this->mapper->toPersistence($payment, $model);
            $model->save();

            RefundModel::query()->where('payment_id', $payment->id()->value)->delete();

            foreach ($this->mapper->refundsToPersistence($payment) as $row) {
                $row->save();
            }
        });
    }

    public function findById(EntityId $id): ?Payment
    {
        $model = PaymentModel::query()->with('refunds')->find($id->value);

        return $model === null ? null : $this->mapper->toDomain($model);
    }

    public function getById(EntityId $id): Payment
    {
        return $this->findById($id)
            ?? throw new DomainException(sprintf('Payment %d not found.', $id->value));
    }

    public function findByOrderId(OrderId $orderId): ?Payment
    {
        $model = PaymentModel::query()
            ->with('refunds')
            ->where('order_id', $orderId->value)
            ->first();

        return $model === null ? null : $this->mapper->toDomain($model);
    }
}
