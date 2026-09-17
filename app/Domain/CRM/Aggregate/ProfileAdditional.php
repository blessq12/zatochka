<?php

namespace App\Domain\Crm\Aggregate;

use DateTimeImmutable;

final class ProfileAdditional
{
    public function __construct(
        private ?int $id,
        private ?string $name = null,
        private ?string $phone = null,
        private ?DateTimeImmutable $birthday = null,
        private bool $deleted = false,
    ) {}

    public static function create(
        ?string $name = null,
        ?string $phone = null,
        ?DateTimeImmutable $birthday = null,
    ): self {
        return new self(null, $name, $phone, $birthday);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
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

    public function changeDetails(
        ?string $name,
        ?string $phone,
        ?DateTimeImmutable $birthday,
    ): void {
        $this->name = $name;
        $this->phone = $phone;
        $this->birthday = $birthday;
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
