<?php

namespace App\Domain\Inventory\Entities;

use App\Domain\Inventory\ValueObjects\CategoryId;
use App\Domain\Inventory\ValueObjects\CategoryName;
use App\Domain\Shared\Interfaces\AggregateRoot;
use App\Domain\Inventory\Events\CategoryCreated;
use App\Domain\Inventory\Events\CategoryActivated;
use App\Domain\Inventory\Events\CategoryDeactivated;

class StockCategory implements AggregateRoot
{
    private CategoryId $id;
    private CategoryName $name;
    private ?string $description;
    private ?string $color;
    private int $sortOrder;
    private bool $isActive;
    private bool $isDeleted;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        CategoryId $id,
        CategoryName $name,
        ?string $description = null,
        ?string $color = null,
        int $sortOrder = 0
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->color = $color;
        $this->sortOrder = $sortOrder;
        $this->isActive = true;
        $this->isDeleted = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        CategoryId $id,
        CategoryName $name,
        ?string $description = null,
        ?string $color = null,
        int $sortOrder = 0
    ): self {
        $category = new self($id, $name, $description, $color, $sortOrder);
        $category->recordEvent(new CategoryCreated(
            $category->id,
            $category->name,
            $category->description,
            $category->color,
            $category->sortOrder
        ));
        return $category;
    }

    public static function reconstitute(
        CategoryId $id,
        CategoryName $name,
        ?string $description,
        ?string $color,
        int $sortOrder,
        bool $isActive,
        bool $isDeleted,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        $category = new self($id, $name, $description, $color, $sortOrder);
        $category->isActive = $isActive;
        $category->isDeleted = $isDeleted;
        $category->createdAt = $createdAt;
        $category->updatedAt = $updatedAt;
        return $category;
    }

    // Getters
    public function id(): CategoryId
    {
        return $this->id;
    }
    public function name(): CategoryName
    {
        return $this->name;
    }
    public function description(): ?string
    {
        return $this->description;
    }
    public function color(): ?string
    {
        return $this->color;
    }
    public function sortOrder(): int
    {
        return $this->sortOrder;
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

    // Business methods
    public function activate(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot activate deleted category');
        }
        if ($this->isActive) {
            return;
        }
        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();
        $this->recordEvent(new CategoryActivated($this->id));
    }

    public function deactivate(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot deactivate deleted category');
        }
        if (!$this->isActive) {
            return;
        }
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();
        $this->recordEvent(new CategoryDeactivated($this->id));
    }

    public function markDeleted(): void
    {
        if ($this->isDeleted) {
            return;
        }
        $this->isDeleted = true;
        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateName(CategoryName $newName): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted category');
        }
        if ($this->name->equals($newName)) {
            return;
        }
        $this->name = $newName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateDescription(?string $newDescription): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted category');
        }
        if ($this->description === $newDescription) {
            return;
        }
        $this->description = $newDescription;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateColor(?string $newColor): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted category');
        }
        if ($this->color === $newColor) {
            return;
        }
        $this->color = $newColor;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateSortOrder(int $newSortOrder): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted category');
        }
        if ($this->sortOrder === $newSortOrder) {
            return;
        }
        $this->sortOrder = $newSortOrder;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function canBeDeleted(): bool
    {
        // Логика проверки возможности удаления (например, нет связанных товаров)
        return true;
    }

    // Event handling
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
