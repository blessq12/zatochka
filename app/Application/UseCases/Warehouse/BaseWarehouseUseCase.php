<?php

namespace App\Application\UseCases\Warehouse;

use App\Application\UseCases\UseCaseInterface;
use App\Domain\Warehouse\Repository\WarehouseRepository;

abstract class BaseWarehouseUseCase implements UseCaseInterface
{
    protected array $data;
    protected WarehouseRepository $warehouseRepository;

    public function __construct()
    {
        $this->warehouseRepository = app(WarehouseRepository::class);
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

    abstract public function validateSpecificData(): self;

    public function execute(): mixed
    {
        return $this->data;
    }
}
