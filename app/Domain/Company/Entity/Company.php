<?php

namespace App\Domain\Company\Entity;

readonly class Company
{
    public function __construct(
        public ?int $id,
        public string $name,
        public string $legalName,
        public string $inn,
        public ?string $kpp,
        public ?string $ogrn,
        public string $legalAddress,
        public ?string $description,
        public ?string $website,
        public ?string $phone,
        public ?string $email,
        public ?string $bankName,
        public ?string $bankBik,
        public ?string $bankAccount,
        public ?string $bankCorAccount,
        public ?string $logoPath,
        public array $additionalData = [],
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

    public function getName(): string
    {
        return $this->name;
    }

    public function getLegalName(): string
    {
        return $this->legalName;
    }

    public function getInn(): string
    {
        return $this->inn;
    }

    public function getKpp(): ?string
    {
        return $this->kpp;
    }

    public function getOgrn(): ?string
    {
        return $this->ogrn;
    }

    public function getLegalAddress(): string
    {
        return $this->legalAddress;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function getBankBik(): ?string
    {
        return $this->bankBik;
    }

    public function getBankAccount(): ?string
    {
        return $this->bankAccount;
    }

    public function getBankCorAccount(): ?string
    {
        return $this->bankCorAccount;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function getAdditionalData(): array
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

    public function hasBankDetails(): bool
    {
        return !empty($this->bankName) &&
            !empty($this->bankBik) &&
            !empty($this->bankAccount);
    }

    public function hasContactInfo(): bool
    {
        return !empty($this->phone) || !empty($this->email);
    }

    public function getDisplayName(): string
    {
        return $this->name !== $this->legalName
            ? "{$this->name} ({$this->legalName})"
            : $this->name;
    }

    public function getShortLegalName(): ?string
    {
        return $this->additionalData['short_legal_name'] ?? null;
    }

    public function getBankInn(): ?string
    {
        return $this->additionalData['bank_inn'] ?? null;
    }

    public function getBankKpp(): ?string
    {
        return $this->additionalData['bank_kpp'] ?? null;
    }

    public function getAccountOpenDate(): ?string
    {
        return $this->additionalData['account_open_date'] ?? null;
    }

    public function isMainCompany(): bool
    {
        return $this->additionalData['is_main'] ?? false;
    }
}
