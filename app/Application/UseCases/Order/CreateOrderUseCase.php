<?php

namespace App\Application\UseCases\Order;

class CreateOrderUseCase extends BaseOrderUseCase
{

    public function validateSpecificData(): self
    {
        return $this;
    }

    public function execute(): mixed
    {
        return $this->data;
    }
}
