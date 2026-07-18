<?php

namespace App\Application\Finance\Query;

use DateTimeImmutable;

final readonly class GetCashDeskSummaryQuery
{
    public function __construct(
        public DateTimeImmutable $from,
        public DateTimeImmutable $to,
        public string $currency = 'RUB',
        public int $recentLimit = 15,
        public ?string $paymentMethod = null,
    ) {}
}
