<?php

namespace App\Application\CRM\DTO;

final readonly class EquipmentPartDTO
{
    public function __construct(
        public int $componentId,
        public string $name,
        public ?string $serialNumber = null,
    ) {}
}
