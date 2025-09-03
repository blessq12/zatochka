<?php

namespace App\Domain\Company\Entities;

use App\Domain\Company\ValueObjects\CompanyName;
use App\Domain\Company\ValueObjects\LegalName;
use App\Domain\Company\ValueObjects\INN;
use App\Domain\Company\Events\CompanyCreated;
use App\Domain\Company\Events\CompanyActivated;
use App\Domain\Company\Events\CompanyDeactivated;
use App\Domain\Shared\Interfaces\AggregateRoot;
use App\Domain\Company\Entities\Branch;

class Company implements AggregateRoot
{
    private int $id;
    private CompanyName $name;
    private LegalName $legalName;
    private INN $inn;
    private ?string $kpp;
    private ?string $ogrn;
    private string $legalAddress;
    private ?string $description;
    private ?string $website;
    private ?string $phone;
    private ?string $email;
    private ?string $bankName;
    private ?string $bankBik;
    private ?string $bankAccount;
    private ?string $bankCorAccount;
    private ?string $logoPath;
    private array $additionalData;
    private bool $isActive;
    private bool $isDeleted;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private function __construct(
        int $id,
        CompanyName $name,
        LegalName $legalName,
        INN $inn,
        string $legalAddress,
        ?string $kpp = null,
        ?string $ogrn = null,
        ?string $description = null,
        ?string $website = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $bankName = null,
        ?string $bankBik = null,
        ?string $bankAccount = null,
        ?string $bankCorAccount = null,
        ?string $logoPath = null,
        array $additionalData = []
    ) {
        $this->id = $id;
        $this->name = $name;
        $this->legalName = $legalName;
        $this->inn = $inn;
        $this->kpp = $kpp;
        $this->ogrn = $ogrn;
        $this->legalAddress = $legalAddress;
        $this->description = $description;
        $this->website = $website;
        $this->phone = $phone;
        $this->email = $email;
        $this->bankName = $bankName;
        $this->bankBik = $bankBik;
        $this->bankAccount = $bankAccount;
        $this->bankCorAccount = $bankCorAccount;
        $this->logoPath = $logoPath;
        $this->additionalData = $additionalData;
        $this->isActive = true;
        $this->isDeleted = false;
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    public static function create(
        CompanyName $name,
        LegalName $legalName,
        INN $inn,
        string $legalAddress,
        ?string $description = null,
        ?string $website = null,
        ?string $phone = null,
        ?string $email = null,
        ?string $bankName = null,
        ?string $bankBik = null,
        ?string $bankAccount = null,
        ?string $bankCorAccount = null,
        ?string $logoPath = null,
        array $additionalData = []
    ): self {
        $company = new self(
            0, // Временный ID, будет заменен при сохранении
            $name,
            $legalName,
            $inn,
            $legalAddress,
            null, // kpp
            null, // ogrn
            $description,
            $website,
            $phone,
            $email,
            $bankName,
            $bankBik,
            $bankAccount,
            $bankCorAccount,
            $logoPath,
            $additionalData
        );

        $company->recordEvent(new CompanyCreated(
            $company->id(),
            $company->name(),
            $company->legalName(),
            $company->inn()
        ));

        return $company;
    }

    public static function reconstitute(
        int $id,
        CompanyName $name,
        LegalName $legalName,
        INN $inn,
        ?string $kpp,
        ?string $ogrn,
        string $legalAddress,
        ?string $description,
        ?string $website,
        ?string $phone,
        ?string $email,
        ?string $bankName,
        ?string $bankBik,
        ?string $bankAccount,
        ?string $bankCorAccount,
        ?string $logoPath,
        array $additionalData,
        bool $isActive,
        bool $isDeleted,
        \DateTimeImmutable $createdAt,
        \DateTimeImmutable $updatedAt
    ): self {
        $company = new self(
            $id,
            $name,
            $legalName,
            $inn,
            $kpp,
            $ogrn,
            $legalAddress,
            $description,
            $website,
            $phone,
            $email,
            $bankName,
            $bankBik,
            $bankAccount,
            $bankCorAccount,
            $logoPath,
            $additionalData
        );

        $company->kpp = $kpp;
        $company->ogrn = $ogrn;
        $company->isActive = $isActive;
        $company->isDeleted = $isDeleted;
        $company->createdAt = $createdAt;
        $company->updatedAt = $updatedAt;

        return $company;
    }

    // Getters
    public function id(): int
    {
        return $this->id;
    }
    public function name(): CompanyName
    {
        return $this->name;
    }
    public function legalName(): LegalName
    {
        return $this->legalName;
    }
    public function inn(): INN
    {
        return $this->inn;
    }
    public function kpp(): ?string
    {
        return $this->kpp;
    }
    public function ogrn(): ?string
    {
        return $this->ogrn;
    }
    public function legalAddress(): string
    {
        return $this->legalAddress;
    }
    public function description(): ?string
    {
        return $this->description;
    }
    public function website(): ?string
    {
        return $this->website;
    }
    public function phone(): ?string
    {
        return $this->phone;
    }
    public function email(): ?string
    {
        return $this->email;
    }
    public function bankName(): ?string
    {
        return $this->bankName;
    }
    public function bankBik(): ?string
    {
        return $this->bankBik;
    }
    public function bankAccount(): ?string
    {
        return $this->bankAccount;
    }
    public function bankCorAccount(): ?string
    {
        return $this->bankCorAccount;
    }
    public function logoPath(): ?string
    {
        return $this->logoPath;
    }
    public function additionalData(): array
    {
        return $this->additionalData;
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
            throw new \InvalidArgumentException('Cannot activate deleted company');
        }
        if ($this->isActive) {
            return;
        }

        $this->isActive = true;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new CompanyActivated($this->id));
    }

    public function deactivate(): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot deactivate deleted company');
        }
        if (!$this->isActive) {
            return;
        }

        $this->isActive = false;
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new CompanyDeactivated($this->id));
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

    public function updateName(CompanyName $newName): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted company');
        }
        if ($this->name->equals($newName)) {
            return;
        }

        $this->name = $newName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateLegalName(LegalName $newLegalName): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted company');
        }
        if ($this->legalName->equals($newLegalName)) {
            return;
        }

        $this->legalName = $newLegalName;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateDescription(?string $newDescription): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted company');
        }
        if ($this->description === $newDescription) {
            return;
        }

        $this->description = $newDescription;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateContactInfo(?string $phone, ?string $email, ?string $website): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted company');
        }

        $this->phone = $phone;
        $this->email = $email;
        $this->website = $website;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateBankInfo(
        ?string $bankName,
        ?string $bankBik,
        ?string $bankAccount,
        ?string $bankCorAccount
    ): void {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted company');
        }

        $this->bankName = $bankName;
        $this->bankBik = $bankBik;
        $this->bankAccount = $bankAccount;
        $this->bankCorAccount = $bankCorAccount;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function updateAdditionalData(array $additionalData): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted company');
        }

        $this->additionalData = $additionalData;
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setLogoPath(?string $logoPath): void
    {
        if ($this->isDeleted) {
            throw new \InvalidArgumentException('Cannot update deleted company');
        }

        $this->logoPath = $logoPath;
        $this->updatedAt = new \DateTimeImmutable();
    }

    // Проверки статуса
    public function canBeDeleted(): bool
    {
        // Логика проверки возможности удаления
        // Например, нет активных филиалов, заказов и т.д.
        return true;
    }

    public function isActiveCompany(): bool
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
