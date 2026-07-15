<?php

namespace App\Application\Pricing\ReadPort;

use App\Application\Pricing\DTO\EstimateDTO;

interface EstimateReadPort
{
    public function findById(int $estimateId): ?EstimateDTO;

    public function findByOrderItemId(int $orderItemId): ?EstimateDTO;
}
