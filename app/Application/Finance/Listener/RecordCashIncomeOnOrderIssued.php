<?php

namespace App\Application\Finance\Listener;

use App\Domain\Finance\Aggregate\CashEntry;
use App\Domain\Finance\CashEntrySource;
use App\Domain\Finance\Repository\CashEntryRepository;
use App\Domain\Finance\Repository\OrderPricingRepository;
use App\Shared\IntegrationEvents\OrderIssued;
use DateTimeImmutable;

final readonly class RecordCashIncomeOnOrderIssued
{
    public function __construct(
        private OrderPricingRepository $pricings,
        private CashEntryRepository $cashEntries,
    ) {}

    public function handle(OrderIssued $event): void
    {
        if ($this->cashEntries->findOrderIssueByOrderId($event->orderId) !== null) {
            return;
        }

        $amount = '0.00';
        if ($event->billingType !== 'warranty') {
            $pricing = $this->pricings->findByOrderId($event->orderId);
            if ($pricing !== null) {
                $amount = $pricing->total();
            }
        }

        $entry = CashEntry::income(
            $amount,
            new DateTimeImmutable('now'),
            CashEntrySource::OrderIssue,
            $event->orderId,
            'Выдача заказа #'.$event->orderId,
        );

        $this->cashEntries->save($entry);
    }
}
