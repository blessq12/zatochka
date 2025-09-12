<?php

namespace App\Application\UseCases\Order;

use App\Domain\Order\DTO\CreateOrderDTO;
use App\Domain\Order\Exception\OrderException;

class CreateOrderUseCase extends BaseOrderUseCase
{
    private ?CreateOrderDTO $dto = null;

    public function validateSpecificData(): self
    {
        $this->dto = CreateOrderDTO::fromArray($this->data);

        // Проверяем на дубликаты (если нужно)
        if ($this->orderRepository->checkExists($this->dto->toArray())) {
            throw OrderException::orderAlreadyExists('Order with similar data already exists');
        }

        return $this;
    }

    public function execute(): mixed
    {
        return $this->orderRepository->create($this->dto->toArray());
    }
}
