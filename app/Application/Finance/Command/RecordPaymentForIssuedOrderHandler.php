<?php

namespace App\Application\Finance\Command;

use App\Application\Finance\Port\OrderSettlementPort;
use App\Application\Shared\DomainEventPublisher;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Finance\Entity\Payment;
use App\Domain\Finance\Repository\PaymentRepository;
use App\Domain\Finance\VO\PaymentMethod;
use App\Domain\Finance\VO\PaymentNumber;
use App\Domain\Order\VO\OrderId;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final readonly class RecordPaymentForIssuedOrderHandler
{
    public function __construct(
        private PaymentRepository $payments,
        private OrderSettlementPort $settlements,
        private EntityIdGenerator $ids,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RecordPaymentForIssuedOrderCommand $command): void
    {
        if ($this->payments->findByOrderId(new OrderId($command->orderId)) !== null) {
            return;
        }

        $snapshot = $this->settlements->snapshot($command->orderId);

        if ($snapshot->isWarranty()) {
            return;
        }

        if (! $snapshot->hasChargeableTotal()) {
            return;
        }

        if ($command->paymentMethod === null || trim($command->paymentMethod) === '') {
            throw new DomainException('Payment method is required to record payment for issued order.');
        }

        $acceptedAt = new DateTimeImmutable;

        $payment = Payment::accept(
            $this->ids->next('payment'),
            PaymentNumber::fromSequenceAndDate($this->ids->next('payment_number')->value, $acceptedAt),
            new OrderId($command->orderId),
            $snapshot->orderNumber,
            new Money($snapshot->totalAmount, $snapshot->currency),
            PaymentMethod::from($command->paymentMethod),
            $acceptedAt,
        );

        $this->payments->save($payment);
        $this->events->publish($payment->pullDomainEvents());
    }
}
