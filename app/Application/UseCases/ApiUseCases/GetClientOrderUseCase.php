<?php

namespace App\Application\UseCases\ApiUseCases;

use App\Application\UseCases\ApiUseCases\BaseApiUseCase;

class GetClientOrderUseCase extends BaseApiUseCase
{
    public function validateSpecificData(): self
    {
        return $this;
    }

    public function execute(): mixed
    {
        $orders = $this->orderRepository->getOrdersByClientId($this->data['id']);
        $ordersArray = array_map(fn($order) => $order->toArray(), $orders);
        return $ordersArray;
    }
}
