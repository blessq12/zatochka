<?php

namespace App\Application\Finance\ReadPort;

use App\Application\Finance\DTO\CashDeskSummaryDTO;
use DateTimeImmutable;

interface CashDeskReadPort
{
    public function summarize(
        DateTimeImmutable $from,
        DateTimeImmutable $to,
        string $currency = 'RUB',
        int $recentLimit = 15,
        ?string $paymentMethod = null,
    ): CashDeskSummaryDTO;
}
