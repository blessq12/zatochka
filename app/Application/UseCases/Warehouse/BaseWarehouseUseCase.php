<?php

namespace App\Application\UseCases\Warehouse;

use App\Domain\Warehouse\Repository\WarehouseRepository;
use App\Domain\Warehouse\Repository\StockCategoryRepository;
use App\Domain\Warehouse\Repository\StockItemRepository;
use App\Domain\Warehouse\Repository\StockMovementRepository;
use App\Domain\Warehouse\Mapper\WarehouseMapper;
use App\Domain\Warehouse\Mapper\StockCategoryMapper;
use App\Domain\Warehouse\Mapper\StockItemMapper;
use App\Domain\Warehouse\Mapper\StockMovementMapper;

abstract class BaseWarehouseUseCase implements WarehouseUseCaseInterface
{
    protected array $data;

    // Все репозитории домена Warehouse
    protected WarehouseRepository $warehouseRepository;
    protected StockCategoryRepository $stockCategoryRepository;
    protected StockItemRepository $stockItemRepository;
    protected StockMovementRepository $stockMovementRepository;

    // Все мапперы домена Warehouse
    protected WarehouseMapper $warehouseMapper;
    protected StockCategoryMapper $stockCategoryMapper;
    protected StockItemMapper $stockItemMapper;
    protected StockMovementMapper $stockMovementMapper;

    public function __construct()
    {
        // Подтягиваем все зависимости домена в конструкторе
        $this->warehouseRepository = app(WarehouseRepository::class);
        $this->stockCategoryRepository = app(StockCategoryRepository::class);
        $this->stockItemRepository = app(StockItemRepository::class);
        $this->stockMovementRepository = app(StockMovementRepository::class);

        $this->warehouseMapper = app(WarehouseMapper::class);
        $this->stockCategoryMapper = app(StockCategoryMapper::class);
        $this->stockItemMapper = app(StockItemMapper::class);
        $this->stockMovementMapper = app(StockMovementMapper::class);
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
        // TODO: Implement specific logic
        return $this->data;
    }
}
