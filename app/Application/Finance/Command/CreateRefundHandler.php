<?php

namespace App\Application\Finance\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Finance\Repository\PaymentRepository;
use App\Domain\Finance\Service\RefundPolicyService;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class CreateRefundHandler
{
    public function __construct(
        private PaymentRepository $payments,
        private RefundPolicyService $refundPolicy,
        private DomainEventPublisher $events,
    ) {}

    public function handle(CreateRefundCommand $command): void
    {
        $payment = $this->payments->getById(new EntityId($command->paymentId));
        $this->refundPolicy->refund(
            $payment,
            new EntityId($command->refundId),
            new Money($command->amount, $command->currency),
            $command->reason,
        );
        $this->payments->save($payment);
        $this->events->publish($payment->pullDomainEvents());
    }
}
