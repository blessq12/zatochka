<?php

namespace App\Domain\Company\Entities;

use App\Domain\Company\ValueObjects\BranchCode;
use App\Domain\Company\ValueObjects\WorkingSchedule;
use App\Domain\Company\Events\BranchCreated;
use App\Domain\Company\Events\BranchActivated;
use App\Domain\Company\Events\BranchDeactivated;
use App\Domain\Company\Events\BranchSetAsMain;
use App\Domain\Shared\Interfaces\AggregateRoot;

class Branch implements AggregateRoot
{
    private int $id;
    private int $companyId;
    private string $name;
    private BranchCode $code;
    private string $address;
    private ?string $phone;
    private ?string $email;
    private WorkingSchedule $workingSchedule;
    private ?string $openingTime;
    private ?string $closingTime;
    private ?float $latitude;
    private ?float $longitude;
    private ?string $description;
    private array $additionalData;
    private bool $isActive;
    private bool $isMain;
    private int $sortOrder;
    private bool $isDeleted;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        int $id,
        int $companyId,
        string $name,
        BranchCode $code,
        string $address,
        ?string $phone = null,
        ?string $email = null,
        WorkingSchedule $workingSchedule = null,
        ?string $openingTime = null,
        ?string $closingTime = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $description = null,
        array $additionalData = []
    ) {
        $this->id = $id;
        $this->companyId = $companyId;
        $this->name = $name;
        $this->code = $code;
        $this->address = $address;
        $this->phone = $phone;
        $this->email = $email;
        $this->workingSchedule = $workingSchedule ?? WorkingSchedule::createDefault();
        $this->openingTime = $openingTime;
        $this->closingTime = $closingTime;
        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->description = $description;
        $this->additionalData = $additionalData;
        $this->isActive = true;
        $this->isMain = false;
        $this->sortOrder = 0;
        $this->isDeleted = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        int $id,
        int $companyId,
        string $name,
        BranchCode $code,
        string $address,
        ?string $phone = null,
        ?string $email = null,
        WorkingSchedule $workingSchedule = null,
        ?string $openingTime = null,
        ?string $closingTime = null,
        ?float $latitude = null,
        ?float $longitude = null,
        ?string $description = null,
        array $additionalData = []
    ): self {
        $branch = new self(
            $id,
            $companyId,
            $name,
            $code,
            $address,
            $phone,
            $email,
            $workingSchedule,
            $openingTime,
            $closingTime,
            $latitude,
            $longitude,
            $description,
            $additionalData
        );

        $branch->recordEvent(new BranchCreated($branch->id(), $branch->companyId()));

        return $branch;
    }

    public static function reconstitute(
        int $id,
        int $companyId,
        string $name,
        BranchCode $code,
        string $address,
        ?string $phone,
        ?string $email,
        WorkingSchedule $workingSchedule,
        ?string $openingTime,
        ?string $closingTime,
        ?float $latitude,
        ?float $longitude,
        ?string $description,
        array $additionalData,
        bool $isActive,
        bool $isMain,
        int $sortOrder,
        bool $isDeleted,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        $branch = new self(
            $id,
            $companyId,
            $name,
            $code,
            $address,
            $phone,
            $email,
            $workingSchedule,
            $openingTime,
            $closingTime,
            $latitude,
            $longitude,
            $description,
            $additionalData
        );

        $branch->isActive = $isActive;
        $branch->isMain = $isMain;
        $branch->sortOrder = $sortOrder;
        $branch->isDeleted = $isDeleted;
        $branch->createdAt = $createdAt;
        $branch->updatedAt = $updatedAt;

        return $branch;
    }

    // Getters
    public function id(): int
    {
        return $this->id;
    }
    public function companyId(): int
    {
        return $this->companyId;
    }
    public function name(): string
    {
        return $this->name;
    }
    public function code(): BranchCode
    {
        return $this->code;
    }
    public function address(): string
    {
        return $this->address;
    }
    public function phone(): ?string
    {
        return $this->phone;
    }
    public function email(): ?string
    {
        return $this->email;
    }
    public function workingSchedule(): WorkingSchedule
    {
        return $this->workingSchedule;
    }
    public function openingTime(): ?string
    {
        return $this->openingTime;
    }
    public function closingTime(): ?string
    {
        return $this->closingTime;
    }
    public function latitude(): ?float
    {
        return $this->latitude;
    }
    public function longitude(): ?float
    {
        return $this->longitude;
    }
    public function description(): ?string
    {
        return $this->description;
    }
    public function additionalData(): array
    {
        return $this->additionalData;
    }
    public function isActive(): bool
    {
        return $this->isActive;
    }
    public function isMain(): bool
    {
        return $this->isMain;
    }
    public function sortOrder(): int
    {
        return $this->sortOrder;
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
            throw new \InvalidArgumentException('Cannot activate deleted branch');
        }
        if ($this->isActive) {
            return;
        }

        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new BranchActivated($this->id));
    }

    public function deactivate(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot deactivate deleted branch');
        }
        if (!$this->isActive) {
            return;
        }

        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new BranchDeactivated($this->id));
    }

    public function setAsMain(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot set deleted branch as main');
        }
        if ($this->isMain) {
            return;
        }

        $this->isMain = true;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new BranchSetAsMain($this->id, $this->companyId));
    }

    public function unsetAsMain(): void
    {
        if (!$this->isMain) {
            return;
        }

        $this->isMain = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function markDeleted(): void
    {
        if ($this->isDeleted) {
            return;
        }

        $this->isDeleted = true;
        $this->isActive = false;
        $this->isMain = false;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateName(string $newName): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted branch');
        }
        if ($this->name === $newName) {
            return;
        }

        $this->name = $newName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateAddress(string $newAddress): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted branch');
        }
        if ($this->address === $newAddress) {
            return;
        }

        $this->address = $newAddress;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateContactInfo(?string $phone, ?string $email): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted branch');
        }

        $this->phone = $phone;
        $this->email = $email;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateWorkingSchedule(WorkingSchedule $workingSchedule): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted branch');
        }
        if ($this->workingSchedule->equals($workingSchedule)) {
            return;
        }

        $this->workingSchedule = $workingSchedule;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateLocation(?float $latitude, ?float $longitude): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted branch');
        }

        $this->latitude = $latitude;
        $this->longitude = $longitude;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateSortOrder(int $sortOrder): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted branch');
        }
        if ($this->sortOrder === $sortOrder) {
            return;
        }

        $this->sortOrder = $sortOrder;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateDescription(?string $description): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted branch');
        }
        if ($this->description === $description) {
            return;
        }

        $this->description = $description;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateAdditionalData(array $additionalData): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted branch');
        }

        $this->additionalData = $additionalData;
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Методы для работы с расписанием
    public function isWorkingToday(): bool
    {
        return $this->workingSchedule->isWorkingToday();
    }

    public function isWorkingNow(): bool
    {
        return $this->workingSchedule->isWorkingNow();
    }

    public function getWorkingDays(): array
    {
        return $this->workingSchedule->getWorkingDays();
    }

    public function getNextWorkingDay(): ?string
    {
        return $this->workingSchedule->getNextWorkingDay();
    }

    // Проверки статуса
    public function canBeDeleted(): bool
    {
        // Логика проверки возможности удаления
        // Например, нет активных заказов, ремонтов и т.д.
        return true;
    }

    public function isActiveBranch(): bool
    {
        return $this->isActive && !$this->isDeleted;
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
