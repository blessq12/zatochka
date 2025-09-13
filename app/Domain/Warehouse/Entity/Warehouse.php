<?php

namespace App\Domain\Warehouse\Entity;

readonly class Warehouse
{
    public function __construct(
        public ?int $id,
        public int $branchId,
        public string $name,
        public ?string $description,
        public bool $isActive = true,
        public bool $isDeleted = false,
        public ?\DateTime $createdAt = null,
        public ?\DateTime $updatedAt = null,
    ) {}

    // Getters (теперь свойства публичные)
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBranchId(): int
    {
        return $this->branchId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getDescription(): ?string
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

    public function canReceiveItems(): bool
    {
        return $this->isOperational();
    }

    public function canIssueItems(): bool
    {
        return $this->isOperational();
    }

    public function getDisplayName(): string
    {
        return $this->name . ($this->description ? " ({$this->description})" : '');
    }
}
