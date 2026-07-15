<?php

namespace App\Application\Order\Query;

use App\Application\Order\DTO\OrderDTO;
use App\Application\Order\ReadPort\OrderReadPort;

final readonly class GetOrderByIdHandler
{
    public function __construct(
        private OrderReadPort $readPort,
    ) {}

    public function handle(GetOrderByIdQuery $query): ?OrderDTO
    {
        return $this->readPort->findById($query->orderId);
    }
}
