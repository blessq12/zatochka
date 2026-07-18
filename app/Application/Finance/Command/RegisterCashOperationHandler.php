<?php

namespace App\Application\Finance\Command;

use App\Application\Shared\DomainEventPublisher;
use App\Domain\Finance\Entity\CashOperation;
use App\Domain\Finance\Repository\CashOperationRepository;
use App\Domain\Finance\VO\CashOperationType;
use App\Domain\Finance\VO\PaymentMethod;
use App\Shared\ValueObject\EntityId;
use App\Shared\ValueObject\Money;

final readonly class RegisterCashOperationHandler
{
    public function __construct(
        private CashOperationRepository $operations,
        private DomainEventPublisher $events,
    ) {}

    public function handle(RegisterCashOperationCommand $command): void
    {
        if ($command->paymentId !== null
            && $this->operations->findByPaymentId(new EntityId($command->paymentId)) !== null) {
            return;
        }

        if ($command->refundId !== null
            && $this->operations->findByRefundId(new EntityId($command->refundId)) !== null) {
            return;
        }

        $paymentMethod = $command->paymentMethod !== null && $command->paymentMethod !== ''
            ? PaymentMethod::from($command->paymentMethod)
            : null;

        $operation = CashOperation::register(
            new EntityId($command->cashOperationId),
            CashOperationType::from($command->type),
            new Money($command->amount, $command->currency),
            $command->comment,
            paymentId: $command->paymentId !== null ? new EntityId($command->paymentId) : null,
            refundId: $command->refundId !== null ? new EntityId($command->refundId) : null,
            paymentMethod: $paymentMethod,
        );

        $this->operations->save($operation);
        $this->events->publish($operation->pullDomainEvents());
    }
}
