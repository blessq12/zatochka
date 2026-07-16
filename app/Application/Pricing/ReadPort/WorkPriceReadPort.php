<?php

namespace App\Application\Pricing\ReadPort;

use App\Application\Pricing\DTO\WorkPriceDTO;

interface WorkPriceReadPort
{
    public function findByMasterCommentId(int $masterCommentId): ?WorkPriceDTO;

    /**
     * @return list<WorkPriceDTO>
     */
    public function findByOrderId(int $orderId): array;
}
