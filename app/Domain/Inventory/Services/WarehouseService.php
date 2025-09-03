<?php

namespace App\Domain\Inventory\Services;

use App\Domain\Inventory\Entities\Warehouse;
use App\Domain\Inventory\ValueObjects\WarehouseName;
use App\Domain\Inventory\Interfaces\WarehouseRepositoryInterface;
use App\Domain\Shared\Events\EventBusInterface;
use InvalidArgumentException;

class WarehouseService
{
    public function __construct(
        private readonly WarehouseRepositoryInterface $warehouseRepository,
        private readonly EventBusInterface $eventBus
    ) {}

    public function createWarehouse(
        int $id,
        ?int $branchId,
        WarehouseName $name,
        ?string $description = null
    ): Warehouse {
        // Проверяем, что филиал не занят другим складом
        if ($branchId && $this->warehouseRepository->existsByBranchId($branchId)) {
            throw new InvalidArgumentException('Branch already has a warehouse assigned');
        }

        $warehouse = Warehouse::create($id, $branchId, $name, $description);

        $this->warehouseRepository->save($warehouse);

        // Публикуем события
        $this->publishEvents($warehouse);

        return $warehouse;
    }

    public function activateWarehouse(int $id): void
    {
        $warehouse = $this->getWarehouseOrFail($id);
        $warehouse->activate();

        $this->warehouseRepository->save($warehouse);
        $this->publishEvents($warehouse);
    }

    public function deactivateWarehouse(int $id): void
    {
        $warehouse = $this->getWarehouseOrFail($id);
        $warehouse->deactivate();

        $this->warehouseRepository->save($warehouse);
        $this->publishEvents($warehouse);
    }

    public function deleteWarehouse(int $id): void
    {
        $warehouse = $this->getWarehouseOrFail($id);

        if (!$warehouse->canBeDeleted()) {
            throw new InvalidArgumentException('Warehouse cannot be deleted');
        }

        $warehouse->markDeleted();
        $this->warehouseRepository->save($warehouse);
        $this->publishEvents($warehouse);
    }

    public function updateWarehouseName(int $id, WarehouseName $newName): void
    {
        $warehouse = $this->getWarehouseOrFail($id);
        $warehouse->updateName($newName);

        $this->warehouseRepository->save($warehouse);
        $this->publishEvents($warehouse);
    }

    public function updateWarehouseDescription(int $id, ?string $newDescription): void
    {
        $warehouse = $this->getWarehouseOrFail($id);
        $warehouse->updateDescription($newDescription);

        $this->warehouseRepository->save($warehouse);
        $this->publishEvents($warehouse);
    }

    public function assignWarehouseToBranch(int $warehouseId, int $branchId): void
    {
        $warehouse = $this->getWarehouseOrFail($warehouseId);

        // Проверяем, что филиал не занят другим складом
        $existingWarehouse = $this->warehouseRepository->findByBranchId($branchId);
        if ($existingWarehouse && $existingWarehouse->id() !== $warehouseId) {
            throw new InvalidArgumentException('Branch already has a warehouse assigned');
        }

        $warehouse->assignToBranch($branchId);

        $this->warehouseRepository->save($warehouse);
        $this->publishEvents($warehouse);
    }

    public function unassignWarehouseFromBranch(int $warehouseId): void
    {
        $warehouse = $this->getWarehouseOrFail($warehouseId);
        $warehouse->unassignFromBranch();

        $this->warehouseRepository->save($warehouse);
        $this->publishEvents($warehouse);
    }

    public function getWarehouseById(int $id): ?Warehouse
    {
        return $this->warehouseRepository->findById($id);
    }

    public function getWarehouseByBranchId(int $branchId): ?Warehouse
    {
        return $this->warehouseRepository->findByBranchId($branchId);
    }

    public function getAllWarehouses(): array
    {
        return $this->warehouseRepository->findAll();
    }

    public function getActiveWarehouses(): array
    {
        return $this->warehouseRepository->findActive();
    }

    private function getWarehouseOrFail(int $id): Warehouse
    {
        $warehouse = $this->warehouseRepository->findById($id);

        if (!$warehouse) {
            throw new InvalidArgumentException('Warehouse not found');
        }

        return $warehouse;
    }

    private function publishEvents(Warehouse $warehouse): void
    {
        if ($warehouse->hasEvents()) {
            $events = $warehouse->pullEvents();
            foreach ($events as $event) {
                $this->eventBus->publish($event);
            }
        }
    }
}
