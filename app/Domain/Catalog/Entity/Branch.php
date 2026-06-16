<?php

namespace App\Domain\Catalog\Entity;

final class Branch
{
    public function __construct(
        private ?int $id,
        private string $name,
        private ?string $address,
        private ?string $phone,
        private bool $isActive,
    ) {}

    public function id(): ?int
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function address(): ?string
    {
        return $this->address;
    }

    public function phone(): ?string
    {
        return $this->phone;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }
}
