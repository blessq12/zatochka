<?php

namespace App\Domain\Equipment\Entity;

use App\Domain\Equipment\VO\SerialNumber;
use App\Shared\Domain\DomainException;
use App\Shared\ValueObject\EntityId;

final class EquipmentComponent
{
    private ?SerialNumber $serialNumber = null;

    public function __construct(
        private readonly EntityId $id,
        private readonly string $name,
    ) {
        if (trim($this->name) === '') {
            throw new DomainException('Component name cannot be empty.');
        }
    }

    public function id(): EntityId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function serialNumber(): ?SerialNumber
    {
        return $this->serialNumber;
    }

    public function registerSerialNumber(SerialNumber $serialNumber): void
    {
        if ($this->serialNumber !== null) {
            throw new DomainException('Serial number is already registered for this component.');
        }

        $this->serialNumber = $serialNumber;
    }
}
