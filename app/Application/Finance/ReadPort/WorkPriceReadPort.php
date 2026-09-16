<?php

namespace App\Application\Finance\ReadPort;

use App\Application\Finance\DTO\WorkPriceDTO;

interface WorkPriceReadPort
{
    public function findByPerformedWorkId(int $performedWorkId): ?WorkPriceDTO;

    /**
     * @return list<WorkPriceDTO>
     */
    public function findByOrderId(string $orderId): array;
}
