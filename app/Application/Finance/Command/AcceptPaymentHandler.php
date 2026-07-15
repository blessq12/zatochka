<?php

namespace App\Application\Finance\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Finance\Entity\Payment;
use App\Domain\Finance\Repository\PaymentRepository;
use App\Domain\Finance\VO\PaymentMethod;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class AcceptPaymentHandler
{
    public function __construct(
        private PaymentRepository $payments,
        private DomainEventPublisher $events,
    ) {}

    public function handle(AcceptPaymentCommand $command): void
    {
        $payment = Payment::accept(
            new EntityId($command->paymentId),
            new EntityId($command->orderId),
            new Money($command->amount, $command->currency),
            PaymentMethod::from($command->method),
        );

        $this->payments->save($payment);
        $this->events->publish($payment->pullDomainEvents());
    }
}
