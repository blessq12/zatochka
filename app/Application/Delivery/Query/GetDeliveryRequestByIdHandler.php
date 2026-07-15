<?php

namespace App\Application\Delivery\Query;

use App\Application\Delivery\DTO\DeliveryRequestDTO;
use App\Application\Delivery\ReadPort\DeliveryReadPort;

final readonly class GetDeliveryRequestByIdHandler
{
    public function __construct(
        private DeliveryReadPort $readPort,
    ) {}

    public function handle(GetDeliveryRequestByIdQuery $query): ?DeliveryRequestDTO
    {
        return $this->readPort->findById($query->deliveryRequestId);
    }
}
