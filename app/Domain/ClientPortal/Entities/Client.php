<?php

namespace App\Domain\ClientPortal\Entities;

final class Client
{
    public function __construct(
        private ?int $id,
        private string $phone,
        private string $fullName,
        private ?string $email,
        private ?string $birthDate,
        private ?string $deliveryAddress,
        private bool $requiresPasswordSet,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function phone(): string
    {
        return $this->phone;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function birthDate(): ?string
    {
        return $this->birthDate;
    }

    public function deliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function requiresPasswordSet(): bool
    {
        return $this->requiresPasswordSet;
    }
}
