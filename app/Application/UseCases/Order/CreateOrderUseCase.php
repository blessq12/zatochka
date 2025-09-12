<?php

namespace App\Application\UseCases\Order;

use App\Domain\Order\DTO\CreateOrderDTO;
use App\Domain\Order\Exception\OrderException;
use App\Domain\Order\Service\OrderNumberGeneratorService;

class CreateOrderUseCase extends BaseOrderUseCase
{
    private ?CreateOrderDTO $dto = null;
    private OrderNumberGeneratorService $orderNumberGenerator;

    public function validateSpecificData(): self
    {
        $this->dto = CreateOrderDTO::fromArray($this->data);

        // Генерируем номер заказа если не указан
        if (empty($this->dto->orderNumber)) {
            $this->orderNumberGenerator = app(OrderNumberGeneratorService::class);
            $orderNumber = $this->orderNumberGenerator->generate();

            // Обновляем DTO с новым номером
            $data = $this->dto->toArray();
            $data['order_number'] = $orderNumber;
            $this->dto = CreateOrderDTO::fromArray($data);
        }

        return $this;
    }

    public function execute(): mixed
    {
        return $this->orderRepository->create($this->dto->toArray());
    }
}
