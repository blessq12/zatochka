<?php

namespace App\Domain\Warehouse\Entity;

readonly class StockCategory
{
    public function __construct(
        public ?int $id,
        public int $warehouseId,
        public string $name,
        public ?string $description,
        public string $color,
        public int $sortOrder,
        public bool $isActive = true,
        public bool $isDeleted = false,
        public ?\DateTime $createdAt = null,
        public ?\DateTime $updatedAt = null,
    ) {}

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getWarehouseId(): int
    {
        return $this->warehouseId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getSortOrder(): int
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

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    // Business methods
    public function isOperational(): bool
    {
        return $this->isActive && !$this->isDeleted;
    }

    public function canBeUsed(): bool
    {
        return $this->isOperational();
    }

    public function getDisplayColor(): string
    {
        return $this->color ?: '#6B7280'; // Серый цвет по умолчанию
    }

    public function getDisplayName(): string
    {
        return $this->name . ($this->description ? " ({$this->description})" : '');
    }

    public function hasValidColor(): bool
    {
        return !empty($this->color) && preg_match('/^#[0-9A-Fa-f]{6}$/', $this->color);
    }
}
