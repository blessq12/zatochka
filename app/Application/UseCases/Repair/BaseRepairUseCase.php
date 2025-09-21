<?php

namespace App\Application\UseCases\Repair;

use App\Application\UseCases\UseCaseInterface;
use App\Domain\Repair\Repository\RepairRepository;
use App\Domain\Order\Repository\OrderRepository;
use App\Domain\Company\Repository\UserRepository;

abstract class BaseRepairUseCase implements UseCaseInterface
{
    protected array $data;
    protected RepairRepository $repairRepository;
    protected OrderRepository $orderRepository;
    protected UserRepository $userRepository;

    public function __construct()
    {
        $this->repairRepository = app(RepairRepository::class);
        $this->orderRepository = app(OrderRepository::class);
        $this->userRepository = app(UserRepository::class);
    }

    public function loadData(array $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function validate(): self
    {
        $this->validateSpecificData();
        return $this;
    }

    abstract protected function validateSpecificData(): self;

    public function execute(): mixed
    {
        return $this->data;
    }
}
