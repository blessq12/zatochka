<?php

namespace App\Domain\Repair\Entity;

readonly class Repair
{
    public function __construct(
        public ?int $id,
        public string $number,
        public int $orderId,
        public ?int $masterId,
        public string $status,
        public ?string $description,
        public ?string $diagnosis,
        public ?string $workPerformed,
        public ?string $notes,
        public ?\DateTime $startedAt,
        public ?\DateTime $completedAt,
        public ?\DateTime $estimatedCompletion,
        public array $partsUsed = [],
        public array $additionalData = [],
        public ?int $workTimeMinutes = null,
        public ?float $price = null,
        public bool $isDeleted = false,
        public ?\DateTime $createdAt = null,
        public ?\DateTime $updatedAt = null,
    ) {}

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): string
    {
        return $this->number;
    }

    public function getOrderId(): int
    {
        return $this->orderId;
    }

    public function getMasterId(): ?int
    {
        return $this->masterId;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getDiagnosis(): ?string
    {
        return $this->diagnosis;
    }

    public function getWorkPerformed(): ?string
    {
        return $this->workPerformed;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function getStartedAt(): ?\DateTime
    {
        return $this->startedAt;
    }

    public function getCompletedAt(): ?\DateTime
    {
        return $this->completedAt;
    }

    public function getEstimatedCompletion(): ?\DateTime
    {
        return $this->estimatedCompletion;
    }

    public function getPartsUsed(): array
    {
        return $this->partsUsed;
    }

    public function getAdditionalData(): array
    {
        return $this->additionalData;
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

    public function getWorkTimeMinutes(): ?int
    {
        return $this->workTimeMinutes;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    // Business methods
    public function isActive(): bool
    {
        return !$this->isDeleted;
    }

    public function isInProgress(): bool
    {
        return in_array($this->status, ['in_progress', 'waiting_parts', 'diagnosis']);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function hasPartsUsed(): bool
    {
        return !empty($this->partsUsed);
    }

    public function getDisplayName(): string
    {
        return "Ремонт #{$this->number}";
    }

    public function getDuration(): ?int
    {
        if (!$this->startedAt || !$this->completedAt) {
            return null;
        }

        return $this->completedAt->getTimestamp() - $this->startedAt->getTimestamp();
    }

    public function isOverdue(): bool
    {
        if (!$this->estimatedCompletion) {
            return false;
        }

        return $this->estimatedCompletion < new \DateTime() && !$this->isCompleted();
    }
}
