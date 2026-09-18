<?php

namespace App\Domain\Crm\Entity;

final class EquipmentModule
{
    public function __construct(
        private ?int $id,
        private string $name,
        private string $serialNumber,
    ) {}

    public static function create(string $name, string $serialNumber): self
    {
        return new self(null, $name, $serialNumber);
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function assignId(int $id): void
    {
        $this->id = $id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function serialNumber(): string
    {
        return $this->serialNumber;
    }
}
