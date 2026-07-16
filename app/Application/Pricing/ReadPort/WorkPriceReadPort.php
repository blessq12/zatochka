<?php

namespace App\Application\Pricing\ReadPort;

use App\Application\Pricing\DTO\WorkPriceDTO;

interface WorkPriceReadPort
{
    public function findByPerformedWorkId(int $performedWorkId): ?WorkPriceDTO;

    /**
     * @return list<WorkPriceDTO>
     */
    public function findByOrderId(string $orderId): array;
}
