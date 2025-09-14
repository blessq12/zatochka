<?php

namespace App\Domain\Company\Entity;

readonly class Branch
{
    public function __construct(
        public ?int $id,
        public int $companyId,
        public string $name,
        public string $code,
        public string $address,
        public ?string $phone,
        public ?string $email,
        public ?string $workingHours,
        public ?array $workingSchedule,
        public ?string $openingTime,
        public ?string $closingTime,
        public ?float $latitude,
        public ?float $longitude,
        public ?string $description,
        public bool $isActive = true,
        public bool $isMain = false,
        public int $sortOrder = 0,
        public bool $isDeleted = false,
        public ?\DateTime $createdAt = null,
        public ?\DateTime $updatedAt = null,
    ) {}

    // Getters
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompanyId(): int
    {
        return $this->companyId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getAddress(): string
    {
        return $this->address;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getWorkingHours(): ?string
    {
        return $this->workingHours;
    }

    public function getWorkingSchedule(): ?array
    {
        return $this->workingSchedule;
    }

    public function getOpeningTime(): ?string
    {
        return $this->openingTime;
    }

    public function getClosingTime(): ?string
    {
        return $this->closingTime;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function isMain(): bool
    {
        return $this->isMain;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
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

    public function hasContactInfo(): bool
    {
        return !empty($this->phone) || !empty($this->email);
    }

    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    public function getDisplayName(): string
    {
        return $this->isMain ? "{$this->name} (Главный)" : $this->name;
    }

    public function getWorkingDays(): array
    {
        if (!$this->workingSchedule || !is_array($this->workingSchedule)) {
            return [];
        }

        return array_keys(array_filter($this->workingSchedule, function ($isWorking) {
            return $isWorking === true || $isWorking === '1';
        }));
    }

    public function isWorkingToday(): bool
    {
        $today = strtolower(date('l'));
        $workingDays = $this->getWorkingDays();

        return in_array($today, $workingDays);
    }
}
