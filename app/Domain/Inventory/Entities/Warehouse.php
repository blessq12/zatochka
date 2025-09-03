<?php

namespace App\Domain\Inventory\Entities;

use App\Domain\Inventory\ValueObjects\WarehouseId;
use App\Domain\Inventory\ValueObjects\WarehouseName;
use App\Domain\Inventory\ValueObjects\BranchId;
use App\Domain\Shared\Interfaces\AggregateRoot;
use App\Domain\Inventory\Events\WarehouseCreated;
use App\Domain\Inventory\Events\WarehouseActivated;
use App\Domain\Inventory\Events\WarehouseDeactivated;

class Warehouse implements AggregateRoot
{
    private WarehouseId $id;
    private ?BranchId $branchId;
    private WarehouseName $name;
    private ?string $description;
    private bool $isActive;
    private bool $isDeleted;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        WarehouseId $id,
        ?BranchId $branchId,
        WarehouseName $name,
        ?string $description = null
    ) {
        $this->id = $id;
        $this->branchId = $branchId;
        $this->name = $name;
        $this->description = $description;
        $this->isActive = true;
        $this->isDeleted = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        WarehouseId $id,
        ?BranchId $branchId,
        WarehouseName $name,
        ?string $description = null
    ): self {
        $warehouse = new self($id, $branchId, $name, $description);

        // Публикуем событие создания
        $warehouse->recordEvent(new WarehouseCreated(
            $warehouse->id,
            $warehouse->branchId,
            $warehouse->name
        ));

        return $warehouse;
    }

    public static function reconstitute(
        WarehouseId $id,
        ?BranchId $branchId,
        WarehouseName $name,
        ?string $description,
        bool $isActive,
        bool $isDeleted,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        $warehouse = new self($id, $branchId, $name, $description);
        $warehouse->isActive = $isActive;
        $warehouse->isDeleted = $isDeleted;
        $warehouse->createdAt = $createdAt;
        $warehouse->updatedAt = $updatedAt;

        return $warehouse;
    }

    // Геттеры
    public function id(): WarehouseId
    {
        return $this->id;
    }

    public function branchId(): ?BranchId
    {
        return $this->branchId;
    }

    public function name(): WarehouseName
    {
        return $this->name;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isDeleted(): bool
    {
        return $this->isDeleted;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    // Бизнес-методы
    public function activate(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot activate deleted warehouse');
        }

        if ($this->isActive) {
            return; // Уже активен
        }

        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new WarehouseActivated($this->id));
    }

    public function deactivate(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot deactivate deleted warehouse');
        }

        if (!$this->isActive) {
            return; // Уже неактивен
        }

        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new WarehouseDeactivated($this->id));
    }

    public function markDeleted(): void
    {
        if ($this->isDeleted) {
            return; // Уже удалён
        }

        $this->isDeleted = true;
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateName(WarehouseName $newName): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted warehouse');
        }

        if ($this->name->equals($newName)) {
            return; // Имя не изменилось
        }

        $this->name = $newName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateDescription(?string $newDescription): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted warehouse');
        }

        if ($this->description === $newDescription) {
            return; // Описание не изменилось
        }

        $this->description = $newDescription;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function assignToBranch(BranchId $branchId): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot assign deleted warehouse');
        }

        if ($this->branchId && $this->branchId->equals($branchId)) {
            return; // Уже привязан к этому филиалу
        }

        $this->branchId = $branchId;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function unassignFromBranch(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot unassign deleted warehouse');
        }

        if (!$this->branchId) {
            return; // Уже не привязан к филиалу
        }

        $this->branchId = null;
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Проверки
    public function canBeDeleted(): bool
    {
        // Можно удалить только если нет активных товаров
        // Это будет проверяться в сервисе
        return true;
    }

    public function isAssignedToBranch(): bool
    {
        return $this->branchId !== null;
    }

    // События
    private array $events = [];

    protected function recordEvent(object $event): void
    {
        $this->events[] = $event;
    }

    public function pullEvents(): array
    {
        $events = $this->events;
        $this->events = [];
        return $events;
    }

    public function hasEvents(): bool
    {
        return !empty($this->events);
    }
}
