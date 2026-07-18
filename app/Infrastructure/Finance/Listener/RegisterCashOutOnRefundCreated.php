<?php

namespace App\Infrastructure\Finance\Listener;

use App\Application\Finance\Command\RegisterCashOperationCommand;
use App\Application\Finance\Command\RegisterCashOperationHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Finance\Event\RefundCreated;
use App\Domain\Finance\VO\CashOperationType;

final readonly class RegisterCashOutOnRefundCreated
{
    public function __construct(
        private RegisterCashOperationHandler $registerCashOperation,
        private EntityIdGenerator $ids,
    ) {}

    public function handle(RefundCreated $event): void
    {
        $this->registerCashOperation->handle(new RegisterCashOperationCommand(
            $this->ids->next('cash_operation')->value,
            CashOperationType::Out->value,
            $event->amount->amount,
            $event->amount->currency,
            'Возврат по заказу '.$event->orderNumber,
            refundId: $event->refundId->value,
            paymentMethod: $event->method->value,
        ));
    }
}
