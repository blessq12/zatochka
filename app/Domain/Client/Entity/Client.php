<?php

namespace App\Domain\Client\Entity;

readonly class Client
{
    public function __construct(
        public ?string $id,
        public string $fullName,
        public string $phone,
        public ?string $email = null,
        public ?string $telegram = null,
        public ?string $birthDate = null,
        public ?string $deliveryAddress = null,
        public ?string $password = null,
        public bool $isDeleted = false,
        public ?\DateTime $createdAt = null,
        public ?\DateTime $updatedAt = null,
        public ?\DateTime $telegramVerifiedAt = null
    ) {}

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getFullName(): string
    {
        return $this->fullName;
    }

    public function getPhone(): string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getTelegram(): ?string
    {
        return $this->telegram;
    }

    public function getBirthDate(): ?string
    {
        return $this->birthDate;
    }

    public function getDeliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function getPassword(): ?string
    {
        return $this->password;
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
    public function getTelegramVerifiedAt(): ?\DateTime
    {
        return $this->telegramVerifiedAt;
    }

    // Password methods
    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    public function verifyPassword(string $password): bool
    {
        if (!$this->hasPassword()) {
            return false;
        }

        return password_verify($password, $this->password);
    }


    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->fullName,
            'phone' => $this->phone,
            'email' => $this->email,
            'telegram' => $this->telegram,
            'birth_date' => $this->birthDate,
            'delivery_address' => $this->deliveryAddress,
            'password' => $this->password,
            'is_deleted' => $this->isDeleted,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
            'telegram_verified_at' => $this->telegramVerifiedAt,
        ];
    }
}
