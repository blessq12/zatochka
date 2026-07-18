<?php

namespace App\Infrastructure\Finance\Listener;

use App\Application\Finance\Command\RecordPaymentForIssuedOrderCommand;
use App\Application\Finance\Command\RecordPaymentForIssuedOrderHandler;
use App\Domain\Order\Event\OrderIssued;

final readonly class RecordPaymentOnOrderIssued
{
    public function __construct(
        private RecordPaymentForIssuedOrderHandler $recordPayment,
    ) {}

    public function handle(OrderIssued $event): void
    {
        $this->recordPayment->handle(new RecordPaymentForIssuedOrderCommand(
            $event->orderId->value,
            $event->paymentMethod,
        ));
    }
}
