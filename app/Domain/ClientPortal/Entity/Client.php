<?php

namespace App\Domain\ClientPortal\Entity;

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

    public static function register(
        string $phone,
        string $fullName,
        ?string $email = null,
        ?string $birthDate = null,
        ?string $deliveryAddress = null,
    ): self {
        return new self(
            id: null,
            phone: $phone,
            fullName: $fullName,
            email: $email,
            birthDate: $birthDate,
            deliveryAddress: $deliveryAddress,
            requiresPasswordSet: false,
        );
    }

    public static function createByManager(
        string $phone,
        string $fullName,
        ?string $email = null,
        ?string $birthDate = null,
        ?string $deliveryAddress = null,
    ): self {
        return new self(
            id: null,
            phone: $phone,
            fullName: $fullName,
            email: $email,
            birthDate: $birthDate,
            deliveryAddress: $deliveryAddress,
            requiresPasswordSet: true,
        );
    }

    public function updateProfile(
        string $fullName,
        ?string $email,
        ?string $birthDate,
        ?string $deliveryAddress,
    ): self {
        $clone = clone $this;
        $clone->fullName = $fullName;
        $clone->email = $email;
        $clone->birthDate = $birthDate;
        $clone->deliveryAddress = $deliveryAddress;

        return $clone;
    }

    public function markPasswordSet(): self
    {
        $clone = clone $this;
        $clone->requiresPasswordSet = false;

        return $clone;
    }

    public function assignId(int $id): self
    {
        $clone = clone $this;
        $clone->id = $id;

        return $clone;
    }
}
