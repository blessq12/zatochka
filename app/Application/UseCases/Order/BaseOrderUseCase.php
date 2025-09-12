<?php

namespace App\Application\UseCases\Order;

use App\Application\UseCases\UseCaseInterface;
use App\Domain\Order\Repository\OrderRepository;

abstract class BaseOrderUseCase implements UseCaseInterface
{
    protected array $data;
    protected OrderRepository $orderRepository;

    public function __construct()
    {
        $this->orderRepository = app(OrderRepository::class);
    }

    public function loadData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function validate(): self
    {
        return $this;
    }

    abstract public function validateSpecificData(): self;

    public function execute(): mixed
    {
        return $this->data;
    }
}
