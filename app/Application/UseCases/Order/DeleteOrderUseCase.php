<?php

namespace App\Application\UseCases\Order;

use App\Domain\Order\DTO\DeleteOrderDTO;
use App\Domain\Order\Exception\OrderException;

class DeleteOrderUseCase extends BaseOrderUseCase
{
    private ?DeleteOrderDTO $dto = null;

    public function validateSpecificData(): self
    {
        $this->dto = DeleteOrderDTO::fromArray($this->data);

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
        $this->orderRepository->delete($order);
        return true;
    }
}
