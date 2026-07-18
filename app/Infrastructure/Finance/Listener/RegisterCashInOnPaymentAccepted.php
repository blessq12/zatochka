<?php

namespace App\Infrastructure\Finance\Listener;

use App\Application\Finance\Command\RegisterCashOperationCommand;
use App\Application\Finance\Command\RegisterCashOperationHandler;
use App\Application\Shared\EntityIdGenerator;
use App\Domain\Finance\Event\PaymentAccepted;
use App\Domain\Finance\VO\CashOperationType;

final readonly class RegisterCashInOnPaymentAccepted
{
    public function __construct(
        private RegisterCashOperationHandler $registerCashOperation,
        private EntityIdGenerator $ids,
    ) {}

    public function handle(PaymentAccepted $event): void
    {
        $this->registerCashOperation->handle(new RegisterCashOperationCommand(
            $this->ids->next('cash_operation')->value,
            CashOperationType::In->value,
            $event->amount->amount,
            $event->amount->currency,
            'Оплата заказа '.$event->orderNumber,
            $event->paymentId->value,
            paymentMethod: $event->method->value,
        ));
    }
}
