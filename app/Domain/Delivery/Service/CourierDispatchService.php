<?php

namespace App\Domain\Delivery\Service;

use App\Domain\Delivery\Entity\DeliveryRequest;
use App\Shared\ValueObject\EntityId;

final class CourierDispatchService
{
    public function assign(DeliveryRequest $request, EntityId $courierId): void
    {
        $request->assignCourier($courierId);
    }
}
