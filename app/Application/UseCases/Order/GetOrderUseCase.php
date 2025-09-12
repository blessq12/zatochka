<?php

namespace App\Application\UseCases\Order;

use App\Domain\Order\DTO\GetOrderDTO;
use App\Domain\Order\Exception\OrderException;

class GetOrderUseCase extends BaseOrderUseCase
{
    private ?GetOrderDTO $dto = null;

    public function validateSpecificData(): self
    {
        $this->dto = GetOrderDTO::fromArray($this->data);

        // Проверяем существование заказа
        $order = $this->orderRepository->get($this->dto->id);
        if (!$order) {
            throw OrderException::orderNotFound($this->dto->id);
        }

        return $this;
    }

    public function execute(): mixed
    {
        return $this->orderRepository->get($this->dto->id);
    }
}
