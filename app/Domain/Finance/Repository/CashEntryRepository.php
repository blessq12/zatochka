<?php

namespace App\Domain\Finance\Repository;

use App\Domain\Finance\Aggregate\CashEntry;
use App\Domain\Finance\CashEntryType;
use DateTimeImmutable;

interface CashEntryRepository
{
    public function save(CashEntry $entry): CashEntry;

    public function findById(int $id): ?CashEntry;

    public function findOrderIssueByOrderId(int $orderId): ?CashEntry;

    public function delete(CashEntry $entry): void;

    /**
     * @return list<CashEntry>
     */
    public function list(
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
        ?CashEntryType $type = null,
    ): array;

    /**
     * @return array{income: string, expense: string, net: string, balance: string}
     */
    public function summarize(
        ?DateTimeImmutable $from = null,
        ?DateTimeImmutable $to = null,
    ): array;
}
