<?php

namespace App\Application\CRM\Command;

final readonly class AddComponentCommand
{
    public function __construct(
        public int $equipmentId,
        public int $componentId,
        public string $name,
        public ?string $serialNumber = null,
    ) {}
}
