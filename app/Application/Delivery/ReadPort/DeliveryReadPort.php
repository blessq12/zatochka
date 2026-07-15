<?php

namespace App\Application\Delivery\ReadPort;

use App\Application\Delivery\DTO\DeliveryRequestDTO;

interface DeliveryReadPort
{
    public function findById(int $deliveryRequestId): ?DeliveryRequestDTO;
}
