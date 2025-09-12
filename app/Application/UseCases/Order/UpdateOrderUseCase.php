<?php

namespace App\Application\UseCases\Order;

use App\Domain\Order\DTO\UpdateOrderDTO;
use App\Domain\Order\Exception\OrderException;

class UpdateOrderUseCase extends BaseOrderUseCase
{
    private ?UpdateOrderDTO $dto = null;

    public function validateSpecificData(): self
    {
        $this->dto = UpdateOrderDTO::fromArray($this->data);

        // Проверяем существование заказа
        $order = $this->orderRepository->get($this->dto->id);
        if (!$order) {
            throw OrderException::orderNotFound($this->dto->id);
        }

        return $this;
    }

    public function execute(): mixed
    {
        $order = $this->orderRepository->get($this->dto->id);
        return $this->orderRepository->update($order, $this->dto->toArray());
    }
}
