<?php

namespace App\Domain\Crm\Aggregate;

use DateTimeImmutable;

final class ProfileAdditional
{
    public function __construct(
        private ?int $id,
        private ?string $email = null,
        private ?string $name = null,
        private ?string $phone = null,
        private ?DateTimeImmutable $birthday = null,
        private ?string $deliveryAddress = null,
        private bool $deleted = false,
    ) {}

    public static function create(
        ?string $email = null,
        ?string $name = null,
        ?string $phone = null,
        ?DateTimeImmutable $birthday = null,
        ?string $deliveryAddress = null,
    ): self {
        return new self(null, $email, $name, $phone, $birthday, $deliveryAddress);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function email(): ?string
    {
        return $this->email;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function birthday(): ?DateTimeImmutable
    {
        return $this->birthday;
    }

    public function deliveryAddress(): ?string
    {
        return $this->deliveryAddress;
    }

    public function changeDetails(
        ?string $name,
        ?string $phone,
        ?DateTimeImmutable $birthday,
        ?string $deliveryAddress,
    ): void {
        $this->name = $name;
        $this->phone = $phone;
        $this->birthday = $birthday;
        $this->deliveryAddress = $deliveryAddress;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function markDeleted(): void
    {
        $this->deleted = true;
    }
}
