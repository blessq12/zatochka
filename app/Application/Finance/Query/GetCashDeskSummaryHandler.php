<?php

namespace App\Application\Finance\Query;

use App\Application\Finance\DTO\CashDeskSummaryDTO;
use App\Application\Finance\ReadPort\CashDeskReadPort;

final readonly class GetCashDeskSummaryHandler
{
    public function __construct(
        private CashDeskReadPort $cashDesk,
    ) {}

    public function handle(GetCashDeskSummaryQuery $query): CashDeskSummaryDTO
    {
        return $this->cashDesk->summarize(
            $query->from,
            $query->to,
            $query->currency,
            $query->recentLimit,
            $query->paymentMethod,
        );
    }
}
