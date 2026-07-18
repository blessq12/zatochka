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
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;
use DateTimeImmutable;

final readonly class AcceptPaymentHandler
{
    public function __construct(
        private PaymentRepository $payments,
        private OrderSettlementPort $settlements,
        private EntityIdGenerator $ids,
        private DomainEventPublisher $events,
    ) {}

    public function handle(AcceptPaymentCommand $command): void
    {
        $orderId = new OrderId($command->orderId);
        $snapshot = $this->settlements->snapshot($command->orderId);

        if ($snapshot->isIssued()) {
            throw new DomainException('Issued orders are settled automatically on issue; manual AcceptPayment is forbidden.');
        }

        if ($this->payments->findByOrderId($orderId) !== null) {
            throw new DomainException('Payment for this order already exists.');
        }

        $acceptedAt = new DateTimeImmutable;

        $payment = Payment::accept(
            new EntityId($command->paymentId),
            PaymentNumber::fromSequenceAndDate($this->ids->next('payment_number')->value, $acceptedAt),
            $orderId,
            $snapshot->orderNumber,
            new Money($command->amount, $command->currency),
            PaymentMethod::from($command->method),
            $acceptedAt,
        );

        $this->payments->save($payment);
        $this->events->publish($payment->pullDomainEvents());
    }
}
