<?php

namespace App\Application\Finance\DTO;

final readonly class CashDeskSummaryDTO
{
    /**
     * @param  list<CashOperationListItemDTO>  $recentOperations
     */
    public function __construct(
        public string $periodFrom,
        public string $periodTo,
        public string $currency,
        public string $inTotal,
        public string $outTotal,
        public string $netTotal,
        public int $inCount,
        public int $outCount,
        public array $recentOperations,
    ) {}
}
